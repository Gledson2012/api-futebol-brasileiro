<?php

namespace App\Console\Commands;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Team;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DailyUpdate extends Command
{
    protected $signature = 'espn:daily-update
        {--league= : Slug do campeonato (vazio = todos)}';

    protected $description = 'Atualiza placares de hoje + busca jogos de amanhã (roda todo dia na meia-noite)';

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
            'timeout' => 15,
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);
    }

    public function handle(): int
    {
        $filterSlug = $this->option('league');
        $year = (int) date('Y');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $championships = $filterSlug
            ? Championship::where('slug', $filterSlug)->get()
            : Championship::all();

        if ($championships->isEmpty()) {
            $this->error("Nenhum campeonato encontrado.");
            return Command::FAILURE;
        }

        $this->info("=== Daily Update: " . date('d/m/Y H:i') . " ===");
        $this->newLine();

        $totalScoresUpdated = 0;
        $totalMatchesAdded = 0;

        $bar = $this->output->createProgressBar($championships->count());
        $bar->start();

        foreach ($championships as $championship) {
            $espnLeague = $this->leagueMap[$championship->slug] ?? null;
            if (!$espnLeague) {
                $bar->advance();
                continue;
            }

            $edition = ChampionshipEdition::firstOrCreate([
                'championship_id' => $championship->id,
                'year' => $year,
            ]);

            // 1. Update today's scores
            $scoresUpdated = $this->updateTodayScores($edition, $espnLeague);
            $totalScoresUpdated += $scoresUpdated;

            // 2. Fetch and store tomorrow's matches
            $matchesAdded = $this->fetchAndStoreTomorrow($edition, $espnLeague, $year);
            $totalMatchesAdded += $matchesAdded;

            if ($scoresUpdated > 0 || $matchesAdded > 0) {
                $this->newLine();
                $msg = "  {$championship->name}:";
                if ($scoresUpdated > 0) $msg .= " {$scoresUpdated} placares atualizados";
                if ($matchesAdded > 0) $msg .= " + {$matchesAdded} jogos de amanhã";
                $this->info($msg);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();
        $this->info("Resumo:");
        $this->info("  Placares atualizados: {$totalScoresUpdated}");
        $this->info("  Jogos de amanhã adicionados: {$totalMatchesAdded}");

        return Command::SUCCESS;
    }

    private function updateTodayScores(ChampionshipEdition $edition, string $espnLeague): int
    {
        $url = "https://site.api.espn.com/apis/site/v2/sports/soccer/{$espnLeague}/scoreboard";
        $updated = 0;

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

                $homeTeam = Team::where('name', $home['team']['displayName'] ?? $home['team']['name'] ?? '')->first();
                $awayTeam = Team::where('name', $away['team']['displayName'] ?? $away['team']['name'] ?? '')->first();
                if (!$homeTeam || !$awayTeam) continue;

                $statusDetail = $competition['status']['type'] ?? [];
                $completed = $statusDetail['completed'] ?? false;
                $isInProgress = ($statusDetail['state'] ?? '') === 'in';
                $newStatus = $completed ? 'completed' : ($isInProgress ? 'in_progress' : 'scheduled');

                $homeScore = $home['score'] !== '' ? (int) $home['score'] : null;
                $awayScore = $away['score'] !== '' ? (int) $away['score'] : null;

                $game = Game::where('championship_edition_id', $edition->id)
                    ->where('home_team_id', $homeTeam->id)
                    ->where('away_team_id', $awayTeam->id)
                    ->first();

                if (!$game) continue;

                $changed = false;
                if ($game->home_score !== $homeScore) { $game->home_score = $homeScore; $changed = true; }
                if ($game->away_score !== $awayScore) { $game->away_score = $awayScore; $changed = true; }
                if ($game->status !== $newStatus) { $game->status = $newStatus; $changed = true; }

                if ($changed) { $game->save(); $updated++; }
            }
        } catch (\Exception $e) {
            // silently skip
        }

        return $updated;
    }

    private function fetchAndStoreTomorrow(ChampionshipEdition $edition, string $espnLeague, int $year): int
    {
        $tomorrow = date('Y-m-d');
        $url = "https://site.api.espn.com/apis/site/v2/sports/soccer/{$espnLeague}/scoreboard";
        $added = 0;

        try {
            $response = $this->client->get($url, [
                'query' => ['dates' => $tomorrow],
            ]);
            $data = json_decode($response->getBody(), true);
            $events = $data['events'] ?? [];

            foreach ($events as $event) {
                $competition = $event['competitions'][0] ?? null;
                if (!$competition) continue;

                $home = collect($competition['competitors'])->firstWhere('homeAway', 'home');
                $away = collect($competition['competitors'])->firstWhere('homeAway', 'away');
                if (!$home || !$away) continue;

                $homeTeam = $this->getOrCreateTeam(
                    $home['team']['displayName'] ?? $home['team']['name'] ?? '',
                    $home['team']['abbreviation'] ?? '',
                    $home['team']['logo'] ?? ''
                );
                $awayTeam = $this->getOrCreateTeam(
                    $away['team']['displayName'] ?? $away['team']['name'] ?? '',
                    $away['team']['abbreviation'] ?? '',
                    $away['team']['logo'] ?? ''
                );

                $eventDate = $event['date'] ?? $competition['date'] ?? '';

                $exists = Game::where('championship_edition_id', $edition->id)
                    ->where('home_team_id', $homeTeam->id)
                    ->where('away_team_id', $awayTeam->id)
                    ->where('match_date', $eventDate)
                    ->exists();

                if ($exists) continue;

                $statusDetail = $competition['status']['type'] ?? [];
                $completed = $statusDetail['completed'] ?? false;
                $isInProgress = ($statusDetail['state'] ?? '') === 'in';

                Game::create([
                    'championship_edition_id' => $edition->id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'match_date' => $eventDate,
                    'home_score' => null,
                    'away_score' => null,
                    'round_name' => $competition['altGameNote'] ?? null,
                    'status' => $completed ? 'completed' : ($isInProgress ? 'in_progress' : 'scheduled'),
                ]);

                $added++;
            }
        } catch (\Exception $e) {
            // silently skip
        }

        return $added;
    }

    private function getOrCreateTeam(string $name, string $short, string $logo): Team
    {
        return Team::firstOrCreate(
            ['name' => $name],
            ['short_name' => $short, 'logo_url' => $logo]
        );
    }
}
