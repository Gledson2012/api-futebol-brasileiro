<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;

class GenericEspnScraper extends BaseScraper implements ScraperInterface
{
    public function getStandings(string $url): array
    {
        $html = $this->getHtml($url);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Seleciona os links dentro dos spans com a classe 'hide-mobile' (nomes dos times)
        $nameNodes = $xpath->query('//span[contains(@class, "hide-mobile")]//a');
        
        // Seleciona todas as linhas (tr) da tabela de estatísticas
        $statsRows = $xpath->query('//div[contains(@class, "Table__Scroller")]//tr');

        $teamNames = [];
        foreach ($nameNodes as $node) {
            $teamNames[] = trim($node->textContent);
        }

        $teamCount = count($teamNames);

        if ($teamCount === 0 || $statsRows->length === 0) {
            return $this->fallbackScraping($html);
        }

        $standings = [];
        $rowOffset = 0;

        // Ignora o cabeçalho se houver mais linhas de estatísticas do que times
        if ($statsRows->length > $teamCount) {
            $firstRowSpans = $xpath->query('.//span[contains(@class, "static-value")]', $statsRows->item(0));
            if ($firstRowSpans->length === 0) {
                $rowOffset = 1;
            }
        }

        for ($i = 0; $i < $teamCount; $i++) {
            $rowNode = $statsRows->item($i + $rowOffset);
            if (!$rowNode) continue;

            $spanNodes = $xpath->query('.//span[contains(@class, "static-value")]', $rowNode);
            
            $stats = [];
            foreach ($spanNodes as $span) {
                $stats[] = trim($span->textContent);
            }

            $standings[] = [
                "team_name" => $teamNames[$i],
                "position" => $i + 1,
                "logo_url" => "",
                "played" => (int)($stats[0] ?? 0),
                "won" => (int)($stats[1] ?? 0),
                "drawn" => (int)($stats[2] ?? 0),
                "lost" => (int)($stats[3] ?? 0),
                "goals_for" => (int)($stats[4] ?? 0),
                "goals_against" => (int)($stats[5] ?? 0),
                "goals_diff" => (int)($stats[6] ?? 0),
                "points" => (int)($stats[7] ?? 0),
            ];
        }

        return $standings;
    }

    private function fallbackScraping(string $html): array
    {
        preg_match_all('/<span class="hide-mobile"><a[^>]*>(.*?)<\/a><\/span>/s', $html, $names);
        preg_match_all('/<span class="static-value">(.*?)<\/span>/s', $html, $stats_all);

        $standings = [];
        $team_count = count($names[1]);
        
        for ($i = 0; $i < $team_count; $i++) {
            $offset = $i * 8; 
            $standings[] = [
                "team_name" => strip_tags($names[1][$i]),
                "position" => $i + 1,
                "logo_url" => "",
                "played" => (int)($stats_all[1][$offset] ?? 0),
                "won" => (int)($stats_all[1][$offset + 1] ?? 0),
                "drawn" => (int)($stats_all[1][$offset + 2] ?? 0),
                "lost" => (int)($stats_all[1][$offset + 3] ?? 0),
                "goals_for" => (int)($stats_all[1][$offset + 4] ?? 0),
                "goals_against" => (int)($stats_all[1][$offset + 5] ?? 0),
                "goals_diff" => (int)($stats_all[1][$offset + 6] ?? 0),
                "points" => (int)($stats_all[1][$offset + 7] ?? 0),
            ];
        }

        return $standings;
    }

    public function getMatches(string $url): array
    {
        $response = $this->client->get($url, ['query' => ['dates' => date('Ymd')]]);
        $data = json_decode($response->getBody()->getContents(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['events'])) {
            return [];
        }

        return self::parseEspnEvents($data['events']);
    }

    public static function parseEspnEvents(array $events): array
    {
        $matches = [];
        foreach ($events as $event) {
            $competition = $event['competitions'][0] ?? null;
            if (!$competition) continue;

            $home = collect($competition['competitors'])->firstWhere('homeAway', 'home');
            $away = collect($competition['competitors'])->firstWhere('homeAway', 'away');
            if (!$home || !$away) continue;

            $status = $competition['status']['type'] ?? [];
            $venue = $competition['venue'] ?? [];

            $homeScore = $home['score'] !== '' ? (int) $home['score'] : null;
            $awayScore = $away['score'] !== '' ? (int) $away['score'] : null;

            $matches[] = [
                'home_team' => $home['team']['displayName'] ?? $home['team']['name'] ?? '',
                'home_team_short' => $home['team']['abbreviation'] ?? '',
                'home_logo' => $home['team']['logo'] ?? '',
                'home_score' => $homeScore,
                'away_team' => $away['team']['displayName'] ?? $away['team']['name'] ?? '',
                'away_team_short' => $away['team']['abbreviation'] ?? '',
                'away_logo' => $away['team']['logo'] ?? '',
                'away_score' => $awayScore,
                'match_date' => $event['date'] ?? $competition['date'] ?? null,
                'status' => $status['state'] ?? 'pre',
                'status_detail' => $status['detail'] ?? $status['description'] ?? '',
                'completed' => $status['completed'] ?? false,
                'round_name' => $competition['altGameNote'] ?? null,
                'venue' => $venue['fullName'] ?? $venue['address']['city'] ?? '',
                'city' => $venue['address']['city'] ?? '',
                'country' => $venue['address']['country'] ?? '',
                'external_id' => $event['id'],
            ];
        }

        return $matches;
    }

    public static function parseLiveMatches(array $events): array
    {
        $liveMatches = [];
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
            ];
        }

        return $liveMatches;
    }

    public function getMatchDetails(string $url): array { return []; }
}
