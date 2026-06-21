<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scraper Standings URLs (HTML)
    |--------------------------------------------------------------------------
    |
    | URLs for scraping standings tables. Used by GenericEspnScraper::getStandings()
    | and BrasileiraoScraper. Override via env vars: URL_{SLUG_UPPERCASE}
    |
    */

    'urls' => [
        'brasileirao' => env('URL_BRASILEIRAO', env('URL_SITE_BRASILEIRAO')),
        'premier-league' => env('URL_PREMIER_LEAGUE', 'https://www.espn.com.br/futebol/tabela/_/liga/ENG.1'),
        'champions-league' => env('URL_CHAMPIONS_LEAGUE', 'https://www.espn.com.br/futebol/tabela/_/liga/UEFA.CHAMPIONS'),
        'world-cup' => env('URL_WORLD_CUP', 'https://www.espn.com.br/futebol/tabela/_/liga/FIFA.WORLD'),
        'la-liga' => env('URL_LA_LIGA', 'https://www.espn.com.br/futebol/tabela/_/liga/ESP.1'),
        'serie-a-italy' => env('URL_SERIE_A_ITALY', 'https://www.espn.com.br/futebol/tabela/_/liga/ITA.1'),
        'bundesliga' => env('URL_BUNDESLIGA', 'https://www.espn.com.br/futebol/tabela/_/liga/GER.1'),
        'club-world-cup' => env('URL_CLUB_WORLD_CUP', 'https://www.espn.com.br/futebol/tabela/_/liga/FIFA.CLUB.WORLD'),
        'libertadores' => env('URL_LIBERTADORES', 'https://www.espn.com.br/futebol/tabela/_/liga/CONMEBOL.LIBERTADORES'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ESPN API URLs (for matches)
    |--------------------------------------------------------------------------
    |
    | ESPN public JSON API endpoints for scoreboard data. Used by
    | GenericEspnScraper::getMatches() to fetch match data directly
    | without HTML parsing.
    |
    */

    'api_urls' => [
        'brasileirao' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/bra.1/scoreboard',
        'premier-league' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/eng.1/scoreboard',
        'champions-league' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/uefa.champions/scoreboard',
        'world-cup' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.world/scoreboard',
        'la-liga' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/esp.1/scoreboard',
        'serie-a-italy' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/ita.1/scoreboard',
        'bundesliga' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/ger.1/scoreboard',
        'club-world-cup' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/fifa.club.world/scoreboard',
        'libertadores' => 'https://site.api.espn.com/apis/site/v2/sports/soccer/conmebol.libertadores/scoreboard',
    ],

];
