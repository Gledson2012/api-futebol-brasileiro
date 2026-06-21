<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;

class GenericEspnScraper extends BaseScraper implements ScraperInterface
{
    public function getStandings(string $url): array
    {
        $html = $this->getHtml($url);

        // A ESPN divide a tabela em duas: nomes das equipas (esquerda) e as estatísticas (direita).
        // Capturamos os blocos de cada tabela.
        preg_match('/<div class="Table__Scroller">(.*?)<\/div>/s', $html, $stats_table_match);
        preg_match('/<div class="Table__Title">(.*?)<\/div>/s', $html, $names_table_match);
        
        // Se não encontrar os blocos, tenta a abordagem anterior (mais genérica)
        if (empty($stats_table_match) || empty($names_table_match)) {
            return $this->fallbackScraping($html);
        }

        // Nomes das equipas
        preg_match_all('/<span class="hide-mobile"><a[^>]*>(.*?)<\/a><\/span>/s', $names_table_match[1], $names);
        
        // Linhas de estatísticas
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/s', $stats_table_match[1], $rows);

        $standings = [];
        $team_count = count($names[1]);
        
        // Ignora a primeira linha se for o cabeçalho
        $row_offset = (count($rows[1]) > $team_count) ? 1 : 0;

        for ($i = 0; $i < $team_count; $i++) {
            $row_content = $rows[1][$i + $row_offset] ?? '';
            preg_match_all('/<span class="static-value">(.*?)<\/span>/s', $row_content, $stats);
            
            $standings[] = [
                "team_name" => strip_tags($names[1][$i]),
                "position" => $i + 1,
                "logo_url" => "",
                "played" => (int)($stats[1][0] ?? 0),
                "won" => (int)($stats[1][1] ?? 0),
                "drawn" => (int)($stats[1][2] ?? 0),
                "lost" => (int)($stats[1][3] ?? 0),
                "goals_for" => (int)($stats[1][4] ?? 0),
                "goals_against" => (int)($stats[1][5] ?? 0),
                "goals_diff" => (int)($stats[1][6] ?? 0),
                "points" => (int)($stats[1][7] ?? 0),
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
        $response = $this->client->get($url, ['query' => ['dates' => date('Y-m-d')]]);
        $data = json_decode($response->getBody()->getContents(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['events'])) {
            return [];
        }

        $matches = [];
        foreach ($data['events'] as $event) {
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

    public function getMatchDetails(string $url): array { return []; }
}
