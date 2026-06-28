<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;
use Illuminate\Support\Collection;

class BrasileiraoScraper extends GenericEspnScraper implements ScraperInterface
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

    public function getMatchDetails(string $url): array
    {
        // Lógica para estatísticas detalhadas (EstatisticasJogosBrasileirao.php)
        return [];
    }

    private function parseMatch(string $html, int $rodada): array
    {
        $dados_jogo = explode('"', $html);
        $count = count($dados_jogo);
        $jogo_concluido = $count > 80;

        $safeGet = function (int $idx, string $default = ''): string {
            return isset($dados_jogo[$idx]) ? trim($dados_jogo[$idx]) : $default;
        };

        $awayTeamIdx = $jogo_concluido ? 67 : 63;
        if ($awayTeamIdx >= $count) $awayTeamIdx = $count - 1;

        $homeScoreIdx = 46;
        $awayScoreIdx = 50;
        if ($homeScoreIdx >= $count) $homeScoreIdx = $count - 1;
        if ($awayScoreIdx >= $count) $awayScoreIdx = $count - 1;

        $externalId = null;
        if ($jogo_concluido && isset($dados_jogo[57])) {
            $parts = explode("/ao-vivo/", $dados_jogo[57]);
            $externalId = trim(end($parts));
        }

        return [
            "rodada" => $rodada,
            "home_team" => $safeGet(29),
            "away_team" => $safeGet($awayTeamIdx),
            "home_score" => $jogo_concluido && isset($dados_jogo[$homeScoreIdx]) ? (int)$dados_jogo[$homeScoreIdx] : null,
            "away_score" => $jogo_concluido && isset($dados_jogo[$awayScoreIdx]) ? (int)$dados_jogo[$awayScoreIdx] : null,
            "date" => $safeGet(7),
            "stadium" => $safeGet(15),
            "external_id" => $externalId
        ];
    }

    private function parseTeam(string $info_time): array
    {
        $dados_equipa  = explode('"', $info_time);
        $array_replace = [">", "<", "/", "tr", "td", "class", "=", " ", "title", '"'];

        $safeGet = function (array $arr, int $idx, string $default = ''): string {
            return isset($arr[$idx]) ? trim($arr[$idx]) : $default;
        };

        $safeInt = function (array $arr, int $idx): int {
            if (!isset($arr[$idx])) return 0;
            return (int) str_replace($array_replace, "", $arr[$idx]) ?: 0;
        };

        $data = [
            "external_id"    => $safeGet($dados_equipa, 1),
            "position"       => $safeInt($dados_equipa, 8),
            "logo_url"       => $safeGet($dados_equipa, 23),
            "team_name"      => $safeGet($dados_equipa, 29),
            "points"         => $safeInt($dados_equipa, 38),
            "played"         => $safeInt($dados_equipa, 40),
            "won"            => $safeInt($dados_equipa, 42),
            "drawn"          => $safeInt($dados_equipa, 44),
            "lost"           => $safeInt($dados_equipa, 46),
            "goals_for"      => $safeInt($dados_equipa, 48),
            "goals_against"  => $safeInt($dados_equipa, 50),
            "goals_diff"     => $safeInt($dados_equipa, 52)
        ];

        return array_map("html_entity_decode", $data);
    }
}
