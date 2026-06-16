<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;

class ChampionsLeagueScraper extends BaseScraper implements ScraperInterface
{
    public function getStandings(string $url): array
    {
        // Implementação específica para Champions League (ex: site da UEFA)
        return [];
    }

    public function getMatches(string $url): array
    {
        return [];
    }

    public function getMatchDetails(string $url): array
    {
        return [];
    }
}
