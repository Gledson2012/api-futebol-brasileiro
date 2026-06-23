<?php

namespace Tests\Feature;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Team;
use App\Services\v2\ChampionshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ChampionshipMatchesCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_matches_caches_results()
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

        $home = Team::create(['name' => 'Arsenal', 'short_name' => 'ARS', 'logo_url' => '']);
        $away = Team::create(['name' => 'Chelsea', 'short_name' => 'CHE', 'logo_url' => '']);

        Game::create([
            'championship_edition_id' => $edition->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'round_name' => '1',
            'match_date' => now(),
            'home_score' => 2,
            'away_score' => 0,
            'status' => 'completed'
        ]);

        $service = app(ChampionshipService::class);
        
        $results1 = $service->getMatches('premier-league', 2026);
        $this->assertCount(1, $results1);

        $versionKey = "championship_matches_version:{$edition->id}";
        $this->assertTrue(Cache::has($versionKey));
        
        $version = Cache::get($versionKey);
        $filtersHash = md5(json_encode([]));
        $cacheKey = "championship_matches:premier-league:2026:v{$version}:{$filtersHash}";
        
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_game_observer_invalidates_cache_by_incrementing_version()
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

        $home = Team::create(['name' => 'Arsenal', 'short_name' => 'ARS', 'logo_url' => '']);
        $away = Team::create(['name' => 'Chelsea', 'short_name' => 'CHE', 'logo_url' => '']);

        $versionKey = "championship_matches_version:{$edition->id}";
        Cache::put($versionKey, 1);

        // Criar uma nova partida deve disparar o GameObserver e incrementar a versão,
        // invalidando implicitamente os caches antigos
        Game::create([
            'championship_edition_id' => $edition->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'round_name' => '1',
            'match_date' => now(),
            'home_score' => 2,
            'away_score' => 0,
            'status' => 'completed'
        ]);

        $this->assertEquals(2, Cache::get($versionKey));
    }
}
