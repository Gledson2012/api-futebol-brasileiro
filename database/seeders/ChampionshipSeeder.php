<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Championship;

class ChampionshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Championship::firstOrCreate([
            'slug' => 'brasileirao'
        ], [
            'name' => 'Campeonato Brasileiro Série A',
            'type' => 'points'
        ]);

        Championship::firstOrCreate([
            'slug' => 'libertadores'
        ], [
            'name' => 'Copa Libertadores',
            'type' => 'mixed'
        ]);
        
        Championship::firstOrCreate([
            'slug' => 'champions-league'
        ], [
            'name' => 'UEFA Champions League',
            'type' => 'mixed'
        ]);

        Championship::firstOrCreate([
            'slug' => 'world-cup'
        ], [
            'name' => 'Copa do Mundo FIFA',
            'type' => 'mixed'
        ]);

        Championship::firstOrCreate([
            'slug' => 'premier-league'
        ], [
            'name' => 'Premier League (Inglaterra)',
            'type' => 'points'
        ]);

        Championship::firstOrCreate([
            'slug' => 'la-liga'
        ], [
            'name' => 'La Liga (Espanha)',
            'type' => 'points'
        ]);

        Championship::firstOrCreate([
            'slug' => 'serie-a-italy'
        ], [
            'name' => 'Serie A (Itália)',
            'type' => 'points'
        ]);

        Championship::firstOrCreate([
            'slug' => 'bundesliga'
        ], [
            'name' => 'Bundesliga (Alemanha)',
            'type' => 'points'
        ]);

        Championship::firstOrCreate([
            'slug' => 'club-world-cup'
        ], [
            'name' => 'Mundial de Clubes FIFA',
            'type' => 'mixed'
        ]);
    }
}
