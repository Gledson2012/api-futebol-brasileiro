<?php

namespace App\Scrapers;

use App\Scrapers\Contracts\ScraperInterface;

class LibertadoresScraper extends BaseScraper implements ScraperInterface
{
    public function getStandings(string $url): array
    {
        // Implementação específica para Libertadores (ex: site da CONMEBOL ou Terra)
        // Por enquanto, retorna array vazio ou lógica de exemplo
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
