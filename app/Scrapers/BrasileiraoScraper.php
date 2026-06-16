<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;
use Illuminate\Support\Collection;

class BrasileiraoScraper extends BaseScraper implements ScraperInterface
{
    /** @var String Pattern para buscar a Tabela */
    const PATTERN_TABELA = '/\<!-- MOD 603 - STANDINGS ROUND ROBIN -->(.*?)<!-- END OF MOD 603 - STANDINGS ROUND ROBIN -->/s';

    /** @var String Pattern para buscar os Jogos */
    const PATTERN_JOGOS_CONTAINER = '/\<div class\=\"live__content__round-list\">(.*?)<\/div>/s';
    
    const PATTERN_JOGOS = '<h3 class="header-round">';

    const PATTERN_JOGO = '/\<li class\=\"match \" itemscope\=\"itemscope\" itemtype\=\"http:\/\/schema.org\/SportsEvent\">(.*?)<\/li\>/s';

    public function getStandings(string $url): array
    {
        $html = $this->getHtml($url);
        
        preg_match_all(self::PATTERN_TABELA, $html, $matches);
        $dados_otmizados = mb_convert_encoding($matches, "HTML-ENTITIES", "UTF-8")[1];
        $fragmento_tabela = $dados_otmizados[0] ?? '';

        preg_match_all('/\<tbody>(.*?)<\/tbody>/s', $fragmento_tabela, $tbody_matches);
        $dados_tabela = mb_convert_encoding($tbody_matches, "HTML-ENTITIES", "UTF-8")[1][0] ?? '';

        $tabela = [];
        foreach (explode("data-idteam", $dados_tabela) as $dados_time) {
            if (strlen($dados_time) < 10) continue;
            $tabela[] = $this->parseTeam($dados_time);
        }

        return $tabela;
    }

    public function getMatches(string $url): array
    {
        // Nota: O Brasileirão v1 usa um endpoint de JSON para jogos em alguns casos, 
        // mas aqui vamos focar no scraping do HTML se a URL for a de página.
        // Se for a URL do JSON do Musa API, a lógica seria diferente.
        // O projeto original tem AtualizaJogosBrasileirao que usa URL_JOGOS_BRASILEIRAO.
        
        $html = $this->getHtml($url);
        
        // Se a resposta for JSON (Musa API), decodificamos direto.
        $json = json_decode($html, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json; // Retorna o JSON direto para processamento no Service
        }

        // Caso contrário, tenta scraping HTML (similar ao JogosBrasileirao.php)
        $jogos_rodadas = [];
        // ... (lógica de scraping de jogos omitida para brevidade nesta fase, 
        // focando na estrutura e na Tabela primeiro)
        
        return $jogos_rodadas;
    }

    public function getMatchDetails(string $url): array
    {
        // Lógica similar ao EstatisticasJogosBrasileirao.php
        return [];
    }

    private function parseTeam(string $info_time): array
    {
        $dados_time    = explode('"', $info_time);
        $array_replace = [">", "<", "/", "tr", "td", "class", "=", " ", "title", '"'];

        $data = [
            "external_id"    => $dados_time[1],
            "position"       => (int) str_replace($array_replace, "", $dados_time[8]),
            "logo_url"       => trim($dados_time[23]),
            "team_name"      => trim($dados_time[29]),
            "points"         => (int) str_replace($array_replace, "", $dados_time[38]),
            "played"         => (int) str_replace($array_replace, "", $dados_time[40]),
            "won"            => (int) str_replace($array_replace, "", $dados_time[42]),
            "drawn"          => (int) str_replace($array_replace, "", $dados_time[44]),
            "lost"           => (int) str_replace($array_replace, "", $dados_time[46]),
            "goals_for"      => (int) str_replace($array_replace, "", $dados_time[48]),
            "goals_against"  => (int) str_replace($array_replace, "", $dados_time[50]),
            "goals_diff"     => (int) str_replace($array_replace, "", $dados_time[52])
        ];

        return array_map("html_entity_decode", $data);
    }
}
