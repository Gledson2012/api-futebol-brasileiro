<?php

namespace App\Repositories\Contracts;

interface ChampionshipRepositoryInterface
{
    public function getAll();
    public function findBySlug(string $slug);
    public function updateStandings(int $editionId, array $standings);
    public function getStandings(int $editionId);
    public function updateMatches(int $editionId, array $matchesData);
    public function getMatches(int $editionId, array $filters = []);
}
