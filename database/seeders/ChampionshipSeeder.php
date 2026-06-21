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
        $championships = [
            ['slug' => 'brasileirao',      'name' => 'Campeonato Brasileiro Série A',  'type' => 'points',  'country' => 'Brasil'],
            ['slug' => 'libertadores',     'name' => 'Copa Libertadores',               'type' => 'mixed',   'country' => 'América do Sul'],
            ['slug' => 'champions-league', 'name' => 'UEFA Champions League',           'type' => 'mixed',   'country' => 'Europa'],
            ['slug' => 'world-cup',        'name' => 'Copa do Mundo FIFA',              'type' => 'mixed',   'country' => 'Mundial'],
            ['slug' => 'premier-league',   'name' => 'Premier League',                  'type' => 'points',  'country' => 'Inglaterra'],
            ['slug' => 'la-liga',          'name' => 'La Liga',                         'type' => 'points',  'country' => 'Espanha'],
            ['slug' => 'serie-a-italy',    'name' => 'Serie A (Itália)',                'type' => 'points',  'country' => 'Itália'],
            ['slug' => 'bundesliga',       'name' => 'Bundesliga',                      'type' => 'points',  'country' => 'Alemanha'],
            ['slug' => 'club-world-cup',   'name' => 'Mundial de Clubes FIFA',          'type' => 'mixed',   'country' => 'Mundial'],
        ];

        foreach ($championships as $data) {
            Championship::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
