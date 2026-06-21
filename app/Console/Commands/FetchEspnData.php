<?php

namespace App\Console\Commands;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class FetchEspnData extends Command
{
    protected $signature = 'espn:fetch-all
        {--year=2026 : Ano da edição}
        {--days=365 : Quantidade de dias para buscar partidas}
        {--clean : Limpar dados existentes antes de importar}';

    protected $description = 'Busca dados reais da ESPN API para todos os campeonatos';

    private Client $client;

    private array $leagueMap = [
        'brasileirao'      => 'bra.1',
        'premier-league'   => 'eng.1',
        'la-liga'          => 'esp.1',
        'serie-a-italy'    => 'ita.1',
        'bundesliga'       => 'ger.1',
        'champions-league' => 'uefa.champions',
        'libertadores'     => 'conmebol.libertadores',
        'world-cup'        => 'fifa.world',
        'club-world-cup'   => 'fifa.club.world',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 15,
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);
    }

    public function handle(): int
    {
        $year = $this->option('year');
        $days = (int) $this->option('days');

        if ($this->option('clean')) {
            $this->cleanData();
        }

        $championships = Championship::all();
        $bar = $this->output->createProgressBar($championships->count());
        $bar->start();

        foreach ($championships as $championship) {
            $espnLeague = $this->leagueMap[$championship->slug] ?? null;
            if (!$espnLeague) {
                $this->newLine();
                $this->warn("Sem mapeamento ESPN para: {$championship->slug}");
                $bar->advance();
                continue;
            }

            $this->newLine();
            $this->info("Processando: {$championship->name} ({$espnLeague})");

            $edition = ChampionshipEdition::firstOrCreate([
                'championship_id' => $championship->id,
                'year' => $year,
            ]);

            $matches = $this->fetchMatches($espnLeague, $year);
            if (empty($matches)) {
                $this->warn("  Nenhuma partida encontrada para {$espnLeague}");
                $bar->advance();
                continue;
            }

            $this->storeMatches($edition, $matches);
            $this->computeAndStoreStandings($edition, $matches);

            $this->info("  {$this->countMatches($edition)} partidas, {$this->countStandings($edition)} times na classificação");
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Dados da ESPN importados com sucesso!');

        return Command::SUCCESS;
    }

    private function fetchMatches(string $league, int $year): array
    {
        $endDate = date('Ymd');
        $startDate = "{$year}0101";

        $url = "https://site.api.espn.com/apis/site/v2/sports/soccer/{$league}/scoreboard";
        $allMatches = [];
        $seen = [];

        try {
            $response = $this->client->get($url, [
                'query' => [
                    'dates' => "{$startDate}-{$endDate}",
                    'limit' => 1000,
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            $events = $data['events'] ?? [];

            foreach ($events as $event) {
                $competition = $event['competitions'][0] ?? null;
                if (!$competition) continue;

                $eventDate = $event['date'] ?? $competition['date'] ?? '';
                $eventYear = date('Y', strtotime($eventDate));
                if ((int) $eventYear !== $year) continue;

                $home = collect($competition['competitors'])->firstWhere('homeAway', 'home');
                $away = collect($competition['competitors'])->firstWhere('homeAway', 'away');
                if (!$home || !$away) continue;

                $eventId = $event['id'] ?? '';
                if (isset($seen[$eventId])) continue;
                $seen[$eventId] = true;

                $statusDetail = $competition['status']['type'] ?? [];
                $completed = $statusDetail['completed'] ?? false;
                $isInProgress = ($statusDetail['state'] ?? '') === 'in';

                $homeScore = $home['score'] !== '' ? (int) $home['score'] : null;
                $awayScore = $away['score'] !== '' ? (int) $away['score'] : null;

                $allMatches[] = [
                    'event_id' => $eventId,
                    'date' => $eventDate,
                    'home_team' => [
                        'name' => $home['team']['displayName'] ?? $home['team']['name'] ?? '',
                        'short' => $home['team']['abbreviation'] ?? '',
                        'logo' => $home['team']['logo'] ?? '',
                    ],
                    'away_team' => [
                        'name' => $away['team']['displayName'] ?? $away['team']['name'] ?? '',
                        'short' => $away['team']['abbreviation'] ?? '',
                        'logo' => $away['team']['logo'] ?? '',
                    ],
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'round_name' => $competition['altGameNote'] ?? null,
                    'status' => $completed ? 'completed' : ($isInProgress ? 'in_progress' : 'scheduled'),
                    'completed' => $completed,
                ];
            }
        } catch (\Exception $e) {
            $this->warn("  Erro ao buscar {$league}: {$e->getMessage()}");
        }

        return $allMatches;
    }

    private function storeMatches(ChampionshipEdition $edition, array $matches): void
    {
        foreach ($matches as $m) {
            $homeTeam = $this->getOrCreateTeam($m['home_team']);
            $awayTeam = $this->getOrCreateTeam($m['away_team']);

            $data = [
                'championship_edition_id' => $edition->id,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'round_name' => $m['round_name'],
                'match_date' => $m['date'],
                'home_score' => $m['home_score'],
                'away_score' => $m['away_score'],
                'status' => $m['status'],
            ];

            Game::updateOrCreate(
                [
                    'championship_edition_id' => $edition->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'match_date' => $m['date'],
                ],
                $data
            );
        }
    }

    private function computeAndStoreStandings(ChampionshipEdition $edition, array $matches): void
    {
        $teams = [];

        foreach ($matches as $m) {
            if (!$m['completed']) continue;

            foreach (['home', 'away'] as $side) {
                $teamName = $m["{$side}_team"]['name'];
                if (!isset($teams[$teamName])) {
                    $teams[$teamName] = [
                        'team_name' => $teamName,
                        'team_short' => $m["{$side}_team"]['short'],
                        'played' => 0,
                        'won' => 0,
                        'drawn' => 0,
                        'lost' => 0,
                        'goals_for' => 0,
                        'goals_against' => 0,
                        'points' => 0,
                    ];
                }

                $scores = [
                    'home' => ['for' => $m['home_score'], 'against' => $m['away_score']],
                    'away' => ['for' => $m['away_score'], 'against' => $m['home_score']],
                ];
                $score = $scores[$side];

                $teams[$teamName]['played']++;
                $teams[$teamName]['goals_for'] += $score['for'];
                $teams[$teamName]['goals_against'] += $score['against'];

                if ($score['for'] > $score['against']) {
                    $teams[$teamName]['won']++;
                    $teams[$teamName]['points'] += 3;
                } elseif ($score['for'] === $score['against']) {
                    $teams[$teamName]['drawn']++;
                    $teams[$teamName]['points'] += 1;
                } else {
                    $teams[$teamName]['lost']++;
                }
            }
        }

        $sorted = collect($teams)->sort(function ($a, $b) {
            if ($a['points'] !== $b['points']) return $b['points'] - $a['points'];
            $gdA = $a['goals_for'] - $a['goals_against'];
            $gdB = $b['goals_for'] - $b['goals_against'];
            if ($gdA !== $gdB) return $gdB - $gdA;
            return $b['goals_for'] - $a['goals_for'];
        })->values();

        Standing::where('championship_edition_id', $edition->id)->delete();

        foreach ($sorted as $position => $teamData) {
            $teamModel = Team::where('name', $teamData['team_name'])->first();
            if (!$teamModel) continue;

            Standing::create([
                'championship_edition_id' => $edition->id,
                'team_id' => $teamModel->id,
                'position' => $position + 1,
                'points' => $teamData['points'],
                'played' => $teamData['played'],
                'won' => $teamData['won'],
                'drawn' => $teamData['drawn'],
                'lost' => $teamData['lost'],
                'goals_for' => $teamData['goals_for'],
                'goals_against' => $teamData['goals_against'],
            ]);
        }
    }

    private function getOrCreateTeam(array $teamData): Team
    {
        return Team::firstOrCreate(
            ['name' => $teamData['name']],
            [
                'short_name' => $teamData['short'],
                'logo_url' => $teamData['logo'],
            ]
        );
    }

    private function cleanData(): void
    {
        $this->info('Limpando dados existentes...');
        Standing::truncate();
        Game::truncate();
        Team::truncate();
        ChampionshipEdition::truncate();
        $this->info('Dados limpos!');
    }

    private function countMatches(ChampionshipEdition $edition): int
    {
        return Game::where('championship_edition_id', $edition->id)->count();
    }

    private function countStandings(ChampionshipEdition $edition): int
    {
        return Standing::where('championship_edition_id', $edition->id)->count();
    }
}
