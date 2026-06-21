<?php

namespace App\Services\v2;

use App\Repositories\Contracts\ChampionshipRepositoryInterface;
use App\Scrapers\ScraperFactory;
use App\Models\ChampionshipEdition;
use Exception;

class ChampionshipService
{
    protected $repository;

    public function __construct(ChampionshipRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getStandings(string $slug, int $year)
    {
        $championship = $this->repository->findBySlug($slug);
        if (!$championship) {
            throw new Exception("Championship not found.");
        }

        $edition = ChampionshipEdition::firstOrCreate([
            'championship_id' => $championship->id,
            'year' => $year
        ]);

        return $this->repository->getStandings($edition->id);
    }

    public function updateStandings(string $slug, int $year, string $url)
    {
        $championship = $this->repository->findBySlug($slug);
        if (!$championship) {
            throw new Exception("Championship not found.");
        }

        $edition = ChampionshipEdition::firstOrCreate([
            'championship_id' => $championship->id,
            'year' => $year
        ]);

        $scraper = ScraperFactory::make($slug);
        $standingsData = $scraper->getStandings($url);

        $this->repository->updateStandings($edition->id, $standingsData);

        return true;
    }

    public function getMatches(string $slug, int $year, array $filters = [])
    {
        $championship = $this->repository->findBySlug($slug);
        if (!$championship) {
            throw new Exception("Championship not found.");
        }

        $edition = ChampionshipEdition::firstOrCreate([
            'championship_id' => $championship->id,
            'year' => $year
        ]);

        return $this->repository->getMatches($edition->id, $filters);
    }

    public function updateMatches(string $slug, int $year, string $url)
    {
        $championship = $this->repository->findBySlug($slug);
        if (!$championship) {
            throw new Exception("Championship not found.");
        }

        $edition = ChampionshipEdition::firstOrCreate([
            'championship_id' => $championship->id,
            'year' => $year
        ]);

        $scraper = ScraperFactory::make($slug);
        $matchesData = $scraper->getMatches($url);

        if (empty($matchesData)) {
            throw new Exception("Nenhuma partida encontrada para {$slug}.");
        }

        $this->repository->updateMatches($edition->id, $matchesData);

        return count($matchesData);
    }
}
