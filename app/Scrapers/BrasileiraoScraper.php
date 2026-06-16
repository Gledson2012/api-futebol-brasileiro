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
        $html = $this->getHtml($url);
        
        // Se for a URL do JSON do Musa API (Terra), decodificamos direto.
        $json = json_decode($html, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $jogos_processados = [];
            $partidas = $json['root']['result']['standings'][0]['teams'] ?? []; // Exemplo de estrutura
            // ... processamento de JSON omitido para brevidade
            return $json; 
        }

        // Scraping HTML (Similar ao JogosBrasileirao.php original)
        preg_match_all(self::PATTERN_JOGOS_CONTAINER, $html, $matches);
        $dados_otmizados = mb_convert_encoding($matches, "HTML-ENTITIES", "UTF-8")[1];
        $fragmento_jogos = $dados_otmizados[0] ?? '';

        $jogos_rodadas = [];
        foreach (explode(self::PATTERN_JOGOS, $fragmento_jogos) as $key => $dados) {
            if ($key === 0) continue;

            $rodada_raw = explode(" ", trim(strip_tags($dados)));
            $rodada = (int) str_replace("&ordf;", "", $rodada_raw[0]);

            if ($rodada > 0 && $rodada <= 38) {
                preg_match_all(self::PATTERN_JOGO, $dados, $partida_matches);
                $partidas = [];
                foreach ($partida_matches[1] as $partida_html) {
                    $partidas[] = $this->parseMatch($partida_html, $rodada);
                }
                $jogos_rodadas[] = [
                    "rodada" => $rodada,
                    "partidas" => $partidas
                ];
            }
        }

        return $jogos_rodadas;
    }

    public function getMatchDetails(string $url): array
    {
        // Lógica para estatísticas detalhadas (EstatisticasJogosBrasileirao.php)
        return [];
    }

    private function parseMatch(string $html, int $rodada): array
    {
        $dados_jogo = explode('"', $html);
        $jogo_concluido = count($dados_jogo) > 80;

        return [
            "rodada" => $rodada,
            "home_team" => trim($dados_jogo[29]),
            "away_team" => $jogo_concluido ? trim($dados_jogo[67]) : trim($dados_jogo[63]),
            "home_score" => $jogo_concluido ? (int)$dados_jogo[46] : null,
            "away_score" => $jogo_concluido ? (int)$dados_jogo[50] : null,
            "date" => trim($dados_jogo[7]),
            "stadium" => trim($dados_jogo[15]),
            "external_id" => $jogo_concluido ? trim(collect(explode("/ao-vivo/", $dados_jogo[57]))->last()) : null
        ];
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
