<?php

namespace App\Repositories;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Standing;
use App\Models\Team;
use App\Repositories\Contracts\ChampionshipRepositoryInterface;

class ChampionshipRepository implements ChampionshipRepositoryInterface
{
    public function getAll()
    {
        return Championship::all();
    }

    public function findBySlug(string $slug)
    {
        return Championship::where('slug', $slug)->first();
    }

    public function updateStandings(int $editionId, array $standingsData)
    {
        foreach ($standingsData as $data) {
            $team = Team::firstOrCreate(
                ['name' => $data['team_name']],
                ['logo_url' => $data['logo_url']]
            );

            Standing::updateOrCreate(
                [
                    'championship_edition_id' => $editionId,
                    'team_id' => $team->id
                ],
                [
                    'position' => $data['position'],
                    'points' => $data['points'],
                    'played' => $data['played'],
                    'won' => $data['won'],
                    'drawn' => $data['drawn'],
                    'lost' => $data['lost'],
                    'goals_for' => $data['goals_for'],
                    'goals_against' => $data['goals_against'],
                ]
            );
        }
    }

    public function getStandings(int $editionId)
    {
        return Standing::with('team')
            ->where('championship_edition_id', $editionId)
            ->orderBy('position')
            ->get();
    }
}
