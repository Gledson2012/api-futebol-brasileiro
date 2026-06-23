<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Helpers\ReturnResponse;
use App\Models\Championship;
use GuzzleHttp\Client;
use Exception;

class LiveMatchesController extends Controller
{
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

    public function index()
    {
        try {
            $client = new Client([
                'timeout' => 10,
                'headers' => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $championships = Championship::all();
            $liveMatches = [];

            foreach ($championships as $championship) {
                $espnLeague = $this->leagueMap[$championship->slug] ?? null;
                if (!$espnLeague) continue;

                try {
                    $response = $client->get(
                        "https://site.api.espn.com/apis/site/v2/sports/soccer/{$espnLeague}/scoreboard"
                    );
                    $data = json_decode($response->getBody(), true);
                    $events = $data['events'] ?? [];

                    foreach ($events as $event) {
                        $competition = $event['competitions'][0] ?? null;
                        if (!$competition) continue;

                        $statusType = $competition['status']['type'] ?? [];
                        $state = $statusType['state'] ?? '';

                        if ($state !== 'in') continue;

                        $home = collect($competition['competitors'])->firstWhere('homeAway', 'home');
                        $away = collect($competition['competitors'])->firstWhere('homeAway', 'away');
                        if (!$home || !$away) continue;

                        $homeScore = $home['score'] !== '' ? (int) $home['score'] : null;
                        $awayScore = $away['score'] !== '' ? (int) $away['score'] : null;

                        $liveMatches[] = [
                            'id' => (int) ($event['id'] ?? 0),
                            'home_team' => [
                                'name' => $home['team']['displayName'] ?? $home['team']['name'] ?? '',
                                'short_name' => $home['team']['abbreviation'] ?? '',
                                'logo_url' => $home['team']['logo'] ?? '',
                            ],
                            'away_team' => [
                                'name' => $away['team']['displayName'] ?? $away['team']['name'] ?? '',
                                'short_name' => $away['team']['abbreviation'] ?? '',
                                'logo_url' => $away['team']['logo'] ?? '',
                            ],
                            'home_score' => $homeScore,
                            'away_score' => $awayScore,
                            'match_date' => $event['date'] ?? $competition['date'] ?? '',
                            'status' => 'in_progress',
                            'round_name' => $competition['altGameNote'] ?? null,
                            'championship_name' => $championship->name,
                            'championship_slug' => $championship->slug,
                        ];
                    }
                } catch (Exception $e) {
                    continue;
                }
            }

            return ReturnResponse::success(
                "Jogos ao vivo retornados com sucesso.",
                $liveMatches,
                count($liveMatches)
            );
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao buscar jogos ao vivo.", [$e->getMessage()]);
        }
    }
}
