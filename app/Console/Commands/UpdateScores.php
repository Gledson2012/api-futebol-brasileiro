<?php

namespace App\Console\Commands;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class UpdateScores extends Command
{
    protected $signature = 'espn:update-scores
        {--league= : Slug do campeonato para atualizar (vazio = todos)}';

    protected $description = 'Atualiza placares e status dos jogos de hoje via ESPN (leve, sem limpar dados)';

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

    private Client $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client([
            'timeout' => 10,
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);
    }

    public function handle(): int
    {
        $filterSlug = $this->option('league');
        $year = (int) date('Y');

        $championships = $filterSlug
            ? Championship::where('slug', $filterSlug)->get()
            : Championship::all();

        if ($championships->isEmpty()) {
            $this->error("Nenhum campeonato encontrado" . ($filterSlug ? " para: {$filterSlug}" : ""));
            return Command::FAILURE;
        }

        $bar = $this->output->createProgressBar($championships->count());
        $bar->start();

        $totalUpdated = 0;

        foreach ($championships as $championship) {
            $espnLeague = $this->leagueMap[$championship->slug] ?? null;
            if (!$espnLeague) {
                $bar->advance();
                continue;
            }

            $edition = ChampionshipEdition::where('championship_id', $championship->id)
                ->where('year', $year)
                ->first();

            if (!$edition) {
                $bar->advance();
                continue;
            }

            $todayMatches = $this->fetchTodayScoreboard($espnLeague);

            if (empty($todayMatches)) {
                $bar->advance();
                continue;
            }

            $updated = $this->updateScores($edition, $todayMatches);
            if ($updated > 0) {
                $this->computeStandings($edition);
                $totalUpdated += $updated;
                $this->newLine();
                $this->info("  {$championship->name}: {$updated} jogos atualizados");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Placares atualizados: {$totalUpdated} jogos");

        return Command::SUCCESS;
    }

    private function fetchTodayScoreboard(string $league): array
    {
        $url = "https://site.api.espn.com/apis/site/v2/sports/soccer/{$league}/scoreboard";
        $allMatches = [];

        try {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);
            $events = $data['events'] ?? [];

            foreach ($events as $event) {
                $competition = $event['competitions'][0] ?? null;
                if (!$competition) continue;

                $home = collect($competition['competitors'])->firstWhere('homeAway', 'home');
                $away = collect($competition['competitors'])->firstWhere('homeAway', 'away');
                if (!$home || !$away) continue;

                $statusDetail = $competition['status']['type'] ?? [];
                $completed = $statusDetail['completed'] ?? false;
                $isInProgress = ($statusDetail['state'] ?? '') === 'in';

                $homeScore = $home['score'] !== '' ? (int) $home['score'] : null;
                $awayScore = $away['score'] !== '' ? (int) $away['score'] : null;

                $allMatches[] = [
                    'event_id' => $event['id'] ?? '',
                    'date' => $event['date'] ?? $competition['date'] ?? '',
                    'home_team_name' => $home['team']['displayName'] ?? $home['team']['name'] ?? '',
                    'away_team_name' => $away['team']['displayName'] ?? $away['team']['name'] ?? '',
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'status' => $completed ? 'completed' : ($isInProgress ? 'in_progress' : 'scheduled'),
                    'completed' => $completed,
                ];
            }
        } catch (\Exception $e) {
            $this->warn("  Erro ESPN {$league}: {$e->getMessage()}");
        }

        return $allMatches;
    }

    private function updateScores(ChampionshipEdition $edition, array $matches): int
    {
        $updated = 0;

        foreach ($matches as $m) {
            $homeTeam = Team::where('name', $m['home_team_name'])->first();
            $awayTeam = Team::where('name', $m['away_team_name'])->first();
            if (!$homeTeam || !$awayTeam) continue;

            $game = Game::where('championship_edition_id', $edition->id)
                ->where('home_team_id', $homeTeam->id)
                ->where('away_team_id', $awayTeam->id)
                ->first();

            if (!$game) continue;

            $changed = false;

            if ($game->home_score !== $m['home_score']) {
                $game->home_score = $m['home_score'];
                $changed = true;
            }
            if ($game->away_score !== $m['away_score']) {
                $game->away_score = $m['away_score'];
                $changed = true;
            }
            if ($game->status !== $m['status']) {
                $game->status = $m['status'];
                $changed = true;
            }

            if ($changed) {
                $game->save();
                $updated++;
            }
        }

        return $updated;
    }

    private function computeStandings(ChampionshipEdition $edition): void
    {
        $matches = Game::where('championship_edition_id', $edition->id)
            ->where('status', 'completed')
            ->get();

        $teams = [];

        foreach ($matches as $game) {
            foreach (['home', 'away'] as $side) {
                $team = $side === 'home' ? $game->homeTeam : $game->awayTeam;
                if (!$team) continue;

                $teamName = $team->name;
                if (!isset($teams[$teamName])) {
                    $teams[$teamName] = [
                        'team_name' => $teamName,
                        'team_short' => $team->short_name,
                        'played' => 0,
                        'won' => 0,
                        'drawn' => 0,
                        'lost' => 0,
                        'goals_for' => 0,
                        'goals_against' => 0,
                        'points' => 0,
                    ];
                }

                $gf = $side === 'home' ? $game->home_score : $game->away_score;
                $ga = $side === 'home' ? $game->away_score : $game->home_score;

                $teams[$teamName]['played']++;
                $teams[$teamName]['goals_for'] += $gf;
                $teams[$teamName]['goals_against'] += $ga;

                if ($gf > $ga) {
                    $teams[$teamName]['won']++;
                    $teams[$teamName]['points'] += 3;
                } elseif ($gf === $ga) {
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
}
