<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Standing;
use App\Models\Team;
use App\Services\v2\ChampionshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChampionshipStandingsCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_standings_caches_results()
    {
        $championship = Championship::create([
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'type' => 'points',
            'country' => 'Inglaterra'
        ]);

        $edition = ChampionshipEdition::create([
            'championship_id' => $championship->id,
            'year' => 2026
        ]);

        $team = Team::create([
            'name' => 'Arsenal',
            'short_name' => 'ARS',
            'logo_url' => 'http://logo.url'
        ]);

        Standing::create([
            'championship_edition_id' => $edition->id,
            'team_id' => $team->id,
            'position' => 1,
            'points' => 3,
            'played' => 1,
            'won' => 1,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 2,
            'goals_against' => 0
        ]);

        $cacheKey = "championship_standings:premier-league:2026";
        $this->assertFalse(Cache::has($cacheKey));

        $service = app(ChampionshipService::class);
        $results1 = $service->getStandings('premier-league', 2026);

        $this->assertCount(1, $results1);
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_standing_observer_clears_cache_on_save()
    {
        $championship = Championship::create([
            'name' => 'Premier League',
            'slug' => 'premier-league',
            'type' => 'points',
            'country' => 'Inglaterra'
        ]);

        $edition = ChampionshipEdition::create([
            'championship_id' => $championship->id,
            'year' => 2026
        ]);

        $team = Team::create([
            'name' => 'Arsenal',
            'short_name' => 'ARS',
            'logo_url' => 'http://logo.url'
        ]);

        $cacheKey = "championship_standings:premier-league:2026";
        Cache::put($cacheKey, ['cached_data']);
        $this->assertTrue(Cache::has($cacheKey));

        // Ao salvar um registro, o cache deve ser limpo automaticamente pelo StandingObserver
        Standing::create([
            'championship_edition_id' => $edition->id,
            'team_id' => $team->id,
            'position' => 1,
            'points' => 3,
            'played' => 1,
            'won' => 1,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 2,
            'goals_against' => 0
        ]);

        $this->assertFalse(Cache::has($cacheKey));
    }
}
