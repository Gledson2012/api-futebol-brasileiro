<?php

namespace App\Repositories\Contracts;

interface ChampionshipRepositoryInterface
{
    public function getAll();
    public function findBySlug(string $slug);
    public function updateStandings(int $editionId, array $standings);
    public function getStandings(int $editionId);
}
