<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;
use App\Models\Championship;
use InvalidArgumentException;

class ScraperFactory
{
    /**
     * Create a scraper instance based on the championship slug.
     *
     * @param string $slug
     * @return ScraperInterface
     */
    public static function make(string $slug): ScraperInterface
    {
        switch ($slug) {
            case 'brasileirao':
                return new BrasileiraoScraper();
            case 'libertadores':
                return new LibertadoresScraper();
            case 'champions-league':
                return new ChampionsLeagueScraper();
            case 'world-cup':
                return new WorldCupScraper();
            case 'premier-league':
                return new PremierLeagueScraper();
            case 'la-liga':
                return new LaLigaScraper();
            case 'serie-a-italy':
                return new SerieAScraper();
            case 'bundesliga':
                return new BundesligaScraper();
            case 'club-world-cup':
                return new ClubWorldCupScraper();
            default:
                throw new InvalidArgumentException("Scraper not implemented for championship: {$slug}");
        }
    }
}
