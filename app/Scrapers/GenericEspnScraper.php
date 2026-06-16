<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;

class GenericEspnScraper extends BaseScraper implements ScraperInterface
{
    public function getStandings(string $url): array
    {
        $html = $this->getHtml($url);

        // Nomes dos times e Abreviações
        preg_match_all('/<span class="hide-mobile"><a[^>]*>(.*?)<\/a><\/span>/s', $html, $names);
        preg_match_all('/<span class="show-mobile"><abbr[^>]*>(.*?)<\/abbr><\/span>/s', $html, $abbrs);
        
        // Estatísticas (Jogos, Vitórias, Empates, Derrotas, Gols Pro, Gols Contra, Saldo, Pontos)
        // Estão em blocos de <td> dentro da segunda tabela
        preg_match_all('/<span class="static-value">(.*?)<\/span>/s', $html, $stats);

        $standings = [];
        $team_count = count($names[1]);
        
        // Cada time na ESPN tem 8 colunas principais de stats
        for ($i = 0; $i < $team_count; $i++) {
            $offset = $i * 8; 
            $standings[] = [
                "team_name" => strip_tags($names[1][$i]),
                "position" => $i + 1,
                "logo_url" => "",
                "played" => (int)($stats[1][$offset] ?? 0),
                "won" => (int)($stats[1][$offset + 1] ?? 0),
                "drawn" => (int)($stats[1][$offset + 2] ?? 0),
                "lost" => (int)($stats[1][$offset + 3] ?? 0),
                "goals_for" => (int)($stats[1][$offset + 4] ?? 0),
                "goals_against" => (int)($stats[1][$offset + 5] ?? 0),
                "goals_diff" => (int)($stats[1][$offset + 6] ?? 0),
                "points" => (int)($stats[1][$offset + 7] ?? 0),
            ];
        }

        return $standings;
    }

    public function getMatches(string $url): array { return []; }
    public function getMatchDetails(string $url): array { return []; }
}
