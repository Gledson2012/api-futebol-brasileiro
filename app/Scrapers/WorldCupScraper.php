<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;

class WorldCupScraper extends BaseScraper implements ScraperInterface
{
    public function getStandings(string $url): array { return []; }
    public function getMatches(string $url): array { return []; }
    public function getMatchDetails(string $url): array { return []; }
}
