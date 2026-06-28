<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Helpers\ReturnResponse;
use App\Models\Championship;
use App\Scrapers\GenericEspnScraper;
use GuzzleHttp\Client;
use Exception;

class LiveMatchesController extends Controller
{
    public function index()
    {
        try {
            $client = new Client([
                'timeout' => 10,
                'headers' => ['User-Agent' => 'Mozilla/5.0'],
            ]);

            $championships = Championship::all();
            $allLiveMatches = [];
            $leagueCodes = config('scrapers.league_codes', []);

            foreach ($championships as $championship) {
                $espnLeague = $leagueCodes[$championship->slug] ?? null;
                if (!$espnLeague) continue;

                try {
                    $response = $client->get(
                        "https://site.api.espn.com/apis/site/v2/sports/soccer/{$espnLeague}/scoreboard"
                    );
                    $data = json_decode($response->getBody(), true);
                    $events = $data['events'] ?? [];

                    $liveMatches = GenericEspnScraper::parseLiveMatches($events);

                    foreach ($liveMatches as &$match) {
                        $match['championship_name'] = $championship->name;
                        $match['championship_slug'] = $championship->slug;
                    }

                    $allLiveMatches = array_merge($allLiveMatches, $liveMatches);
                } catch (Exception $e) {
                    \Log::warning("Failed to fetch live matches for {$championship->slug}: " . $e->getMessage());
                    continue;
                }
            }

            return ReturnResponse::success(
                "Jogos ao vivo retornados com sucesso.",
                $allLiveMatches,
                count($allLiveMatches)
            );
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao buscar jogos ao vivo.", [$e->getMessage()]);
        }
    }
}
