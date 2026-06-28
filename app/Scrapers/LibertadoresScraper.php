<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;

class LibertadoresScraper extends GenericEspnScraper implements ScraperInterface
{
    public function getStandings(string $url): array
    {
        return parent::getStandings($url);
    }

    public function getMatches(string $url): array
    {
        return parent::getMatches($url);
    }
}
