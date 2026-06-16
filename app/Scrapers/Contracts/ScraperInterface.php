<?php

namespace App\Scrapers\Contracts;

interface ScraperInterface
{
    /**
     * Get the standings for a championship edition.
     *
     * @param string $url
     * @return array
     */
    public function getStandings(string $url): array;

    /**
     * Get the matches for a championship edition.
     *
     * @param string $url
     * @return array
     */
    public function getMatches(string $url): array;

    /**
     * Get details for a specific match.
     *
     * @param string $url
     * @return array
     */
    public function getMatchDetails(string $url): array;
}
