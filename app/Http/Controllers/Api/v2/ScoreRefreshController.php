<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Helpers\ReturnResponse;
use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use GuzzleHttp\Client;
use Exception;

class ScoreRefreshController extends Controller
{
    public function refresh()
    {
        try {
            $client = new Client([
                'timeout' => 10,
                'headers' => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $year = (int) date('Y');
            $championships = Championship::all();
            $totalUpdated = 0;
            $leagueCodes = config('scrapers.league_codes', []);

            foreach ($championships as $championship) {
                $espnLeague = $leagueCodes[$championship->slug] ?? null;
                if (!$espnLeague) continue;

                $edition = ChampionshipEdition::where('championship_id', $championship->id)
                    ->where('year', $year)
                    ->first();
                if (!$edition) continue;

                try {
                    $response = $client->get(
                        "https://site.api.espn.com/apis/site/v2/sports/soccer/{$espnLeague}/scoreboard"
                    );
                    $data = json_decode($response->getBody(), true);
                    $events = $data['events'] ?? [];
                } catch (Exception $e) {
                    \Log::warning("Failed to fetch scoreboard for {$championship->slug}: " . $e->getMessage());
                    continue;
                }

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

                    if ($changed) {
                        $game->save();
                        $totalUpdated++;
                    }
                }

                if ($totalUpdated > 0) {
                    $this->computeStandings($edition);
                }
            }

            return ReturnResponse::success(
                "{$totalUpdated} jogos atualizados com sucesso.",
                ['updated' => $totalUpdated]
            );
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao atualizar placares.", [$e->getMessage()]);
        }
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
                        'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                        'goals_for' => 0, 'goals_against' => 0, 'points' => 0,
                    ];
                }

                $gf = $side === 'home' ? $game->home_score : $game->away_score;
                $ga = $side === 'home' ? $game->away_score : $game->home_score;

                $teams[$teamName]['played']++;
                $teams[$teamName]['goals_for'] += $gf;
                $teams[$teamName]['goals_against'] += $ga;

                if ($gf > $ga) { $teams[$teamName]['won']++; $teams[$teamName]['points'] += 3; }
                elseif ($gf === $ga) { $teams[$teamName]['drawn']++; $teams[$teamName]['points'] += 1; }
                else { $teams[$teamName]['lost']++; }
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

        $teamNames = collect($sorted)->pluck('team_name')->toArray();
        $loadedTeams = Team::whereIn('name', $teamNames)->get()->keyBy('name');

        foreach ($sorted as $position => $teamData) {
            $teamModel = $loadedTeams->get($teamData['team_name']);
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
