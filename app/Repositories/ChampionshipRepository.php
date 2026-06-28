<?php

namespace App\Repositories;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use App\Repositories\Contracts\ChampionshipRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($editionId, $standingsData) {
            foreach ($standingsData as $data) {
                $team = Team::firstOrCreate(
                    ['name' => $data['team_name']],
                    ['logo_url' => $data['logo_url'] ?? null]
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
        });
    }

    public function getStandings(int $editionId)
    {
        return Standing::with('team')
            ->where('championship_edition_id', $editionId)
            ->orderBy('position')
            ->get();
    }

    public function updateMatches(int $editionId, array $matchesData)
    {
        return DB::transaction(function () use ($editionId, $matchesData) {
            foreach ($matchesData as $data) {
                $homeTeam = Team::firstOrCreate(
                    ['name' => $data['home_team']],
                    ['logo_url' => $data['home_logo'] ?? '']
                );

                $awayTeam = Team::firstOrCreate(
                    ['name' => $data['away_team']],
                    ['logo_url' => $data['away_logo'] ?? '']
                );

                $matchDate = $data['match_date'] ?? null;
                $matchDate = $matchDate ? date('Y-m-d H:i:s', strtotime($matchDate)) : null;

                Game::updateOrCreate(
                    [
                        'championship_edition_id' => $editionId,
                        'home_team_id' => $homeTeam->id,
                        'away_team_id' => $awayTeam->id,
                        'match_date' => $matchDate,
                    ],
                    [
                        'round_name' => $data['round_name'] ?? null,
                        'home_score' => $data['home_score'] ?? null,
                        'away_score' => $data['away_score'] ?? null,
                        'status' => $data['completed'] ? 'completed' : ($data['status'] ?? 'scheduled'),
                    ]
                );
            }
        });
    }

    public function getMatches(int $editionId, array $filters = [])
    {
        $query = Game::with(['homeTeam', 'awayTeam'])
            ->where('championship_edition_id', $editionId);

        if (!empty($filters['team'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('homeTeam', function ($sq) use ($filters) {
                    $sq->where('name', 'like', "%{$filters['team']}%");
                })->orWhereHas('awayTeam', function ($sq) use ($filters) {
                    $sq->where('name', 'like', "%{$filters['team']}%");
                });
            });
        }

        if (!empty($filters['round'])) {
            $query->where('round_name', $filters['round']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('match_date')->get();
    }
}
