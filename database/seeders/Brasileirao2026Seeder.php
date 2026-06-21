<?php

namespace Database\Seeders;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Database\Seeder;

class Brasileirao2026Seeder extends Seeder
{
    private array $teams = [
        ['name' => 'Flamengo', 'short_name' => 'FLA'],
        ['name' => 'Palmeiras', 'short_name' => 'PAL'],
        ['name' => 'Santos', 'short_name' => 'SAN'],
        ['name' => 'Corinthians', 'short_name' => 'COR'],
        ['name' => 'São Paulo', 'short_name' => 'SAO'],
        ['name' => 'Grêmio', 'short_name' => 'GRE'],
        ['name' => 'Internacional', 'short_name' => 'INT'],
        ['name' => 'Cruzeiro', 'short_name' => 'CRU'],
        ['name' => 'Atlético Mineiro', 'short_name' => 'CAM'],
        ['name' => 'Botafogo', 'short_name' => 'BOT'],
        ['name' => 'Fluminense', 'short_name' => 'FLU'],
        ['name' => 'Vasco da Gama', 'short_name' => 'VAS'],
        ['name' => 'Bahia', 'short_name' => 'BAH'],
        ['name' => 'Athletico Paranaense', 'short_name' => 'CAP'],
        ['name' => 'Fortaleza', 'short_name' => 'FOR'],
        ['name' => 'Ceará', 'short_name' => 'CEA'],
        ['name' => 'Red Bull Bragantino', 'short_name' => 'RBB'],
        ['name' => 'Goiás', 'short_name' => 'GOI'],
        ['name' => 'Cuiabá', 'short_name' => 'CUI'],
        ['name' => 'Vitória', 'short_name' => 'VIT'],
    ];

    public function run(): void
    {
        $championship = Championship::where('slug', 'brasileirao')->first();
        if (!$championship) {
            $this->command->error('Campeonato brasileirao não encontrado. Execute ChampionshipSeeder primeiro.');
            return;
        }

        $edition = ChampionshipEdition::firstOrCreate([
            'championship_id' => $championship->id,
            'year' => 2026,
        ], [
            'start_date' => '2026-04-12',
            'end_date' => '2026-12-06',
        ]);

        $teamIds = [];
        foreach ($this->teams as $t) {
            $team = Team::firstOrCreate(
                ['name' => $t['name']],
                ['short_name' => $t['short_name'], 'logo_url' => '']
            );
            $teamIds[] = $team->id;
        }

        $standingsData = [
            ['position' => 1, 'team_id' => $teamIds[0], 'points' => 42, 'played' => 18, 'won' => 13, 'drawn' => 3, 'lost' => 2, 'goals_for' => 35, 'goals_against' => 12],
            ['position' => 2, 'team_id' => $teamIds[1], 'points' => 38, 'played' => 18, 'won' => 11, 'drawn' => 5, 'lost' => 2, 'goals_for' => 30, 'goals_against' => 14],
            ['position' => 3, 'team_id' => $teamIds[2], 'points' => 34, 'played' => 18, 'won' => 10, 'drawn' => 4, 'lost' => 4, 'goals_for' => 28, 'goals_against' => 18],
            ['position' => 4, 'team_id' => $teamIds[3], 'points' => 32, 'played' => 18, 'won' => 9, 'drawn' => 5, 'lost' => 4, 'goals_for' => 22, 'goals_against' => 16],
            ['position' => 5, 'team_id' => $teamIds[4], 'points' => 31, 'played' => 18, 'won' => 9, 'drawn' => 4, 'lost' => 5, 'goals_for' => 26, 'goals_against' => 19],
            ['position' => 6, 'team_id' => $teamIds[5], 'points' => 29, 'played' => 18, 'won' => 8, 'drawn' => 5, 'lost' => 5, 'goals_for' => 24, 'goals_against' => 20],
            ['position' => 7, 'team_id' => $teamIds[6], 'points' => 28, 'played' => 18, 'won' => 7, 'drawn' => 7, 'lost' => 4, 'goals_for' => 20, 'goals_against' => 17],
            ['position' => 8, 'team_id' => $teamIds[7], 'points' => 26, 'played' => 18, 'won' => 7, 'drawn' => 5, 'lost' => 6, 'goals_for' => 22, 'goals_against' => 21],
            ['position' => 9, 'team_id' => $teamIds[8], 'points' => 25, 'played' => 18, 'won' => 6, 'drawn' => 7, 'lost' => 5, 'goals_for' => 19, 'goals_against' => 18],
            ['position' => 10, 'team_id' => $teamIds[9], 'points' => 24, 'played' => 18, 'won' => 7, 'drawn' => 3, 'lost' => 8, 'goals_for' => 21, 'goals_against' => 23],
            ['position' => 11, 'team_id' => $teamIds[10], 'points' => 23, 'played' => 18, 'won' => 6, 'drawn' => 5, 'lost' => 7, 'goals_for' => 18, 'goals_against' => 20],
            ['position' => 12, 'team_id' => $teamIds[11], 'points' => 22, 'played' => 18, 'won' => 6, 'drawn' => 4, 'lost' => 8, 'goals_for' => 17, 'goals_against' => 24],
            ['position' => 13, 'team_id' => $teamIds[12], 'points' => 21, 'played' => 18, 'won' => 6, 'drawn' => 3, 'lost' => 9, 'goals_for' => 19, 'goals_against' => 25],
            ['position' => 14, 'team_id' => $teamIds[13], 'points' => 20, 'played' => 18, 'won' => 5, 'drawn' => 5, 'lost' => 8, 'goals_for' => 16, 'goals_against' => 22],
            ['position' => 15, 'team_id' => $teamIds[14], 'points' => 19, 'played' => 18, 'won' => 5, 'drawn' => 4, 'lost' => 9, 'goals_for' => 15, 'goals_against' => 24],
            ['position' => 16, 'team_id' => $teamIds[15], 'points' => 18, 'played' => 18, 'won' => 4, 'drawn' => 6, 'lost' => 8, 'goals_for' => 14, 'goals_against' => 22],
            ['position' => 17, 'team_id' => $teamIds[16], 'points' => 17, 'played' => 18, 'won' => 4, 'drawn' => 5, 'lost' => 9, 'goals_for' => 16, 'goals_against' => 26],
            ['position' => 18, 'team_id' => $teamIds[17], 'points' => 16, 'played' => 18, 'won' => 4, 'drawn' => 4, 'lost' => 10, 'goals_for' => 13, 'goals_against' => 27],
            ['position' => 19, 'team_id' => $teamIds[18], 'points' => 14, 'played' => 18, 'won' => 3, 'drawn' => 5, 'lost' => 10, 'goals_for' => 11, 'goals_against' => 28],
            ['position' => 20, 'team_id' => $teamIds[19], 'points' => 12, 'played' => 18, 'won' => 2, 'drawn' => 6, 'lost' => 10, 'goals_for' => 10, 'goals_against' => 30],
        ];

        foreach ($standingsData as $s) {
            Standing::updateOrCreate(
                [
                    'championship_edition_id' => $edition->id,
                    'team_id' => $s['team_id'],
                ],
                [
                    'position' => $s['position'],
                    'points' => $s['points'],
                    'played' => $s['played'],
                    'won' => $s['won'],
                    'drawn' => $s['drawn'],
                    'lost' => $s['lost'],
                    'goals_for' => $s['goals_for'],
                    'goals_against' => $s['goals_against'],
                ]
            );
        }

        $matchesData = [
            ['round' => '1ª Rodada', 'home' => $teamIds[0], 'away' => $teamIds[3], 'home_score' => 2, 'away_score' => 1, 'date' => '2026-04-13 16:00:00', 'status' => 'completed'],
            ['round' => '1ª Rodada', 'home' => $teamIds[1], 'away' => $teamIds[4], 'home_score' => 1, 'away_score' => 0, 'date' => '2026-04-13 18:30:00', 'status' => 'completed'],
            ['round' => '1ª Rodada', 'home' => $teamIds[2], 'away' => $teamIds[5], 'home_score' => 3, 'away_score' => 1, 'date' => '2026-04-13 20:00:00', 'status' => 'completed'],
            ['round' => '1ª Rodada', 'home' => $teamIds[6], 'away' => $teamIds[7], 'home_score' => 0, 'away_score' => 0, 'date' => '2026-04-14 16:00:00', 'status' => 'completed'],
            ['round' => '2ª Rodada', 'home' => $teamIds[3], 'away' => $teamIds[1], 'home_score' => 2, 'away_score' => 2, 'date' => '2026-04-20 16:00:00', 'status' => 'completed'],
            ['round' => '2ª Rodada', 'home' => $teamIds[4], 'away' => $teamIds[0], 'home_score' => 1, 'away_score' => 3, 'date' => '2026-04-20 18:30:00', 'status' => 'completed'],
            ['round' => '2ª Rodada', 'home' => $teamIds[5], 'away' => $teamIds[6], 'home_score' => 2, 'away_score' => 0, 'date' => '2026-04-20 20:00:00', 'status' => 'completed'],
            ['round' => '2ª Rodada', 'home' => $teamIds[7], 'away' => $teamIds[2], 'home_score' => 1, 'away_score' => 1, 'date' => '2026-04-21 16:00:00', 'status' => 'completed'],
            ['round' => '3ª Rodada', 'home' => $teamIds[0], 'away' => $teamIds[2], 'home_score' => 4, 'away_score' => 0, 'date' => '2026-04-27 16:00:00', 'status' => 'completed'],
            ['round' => '3ª Rodada', 'home' => $teamIds[1], 'away' => $teamIds[7], 'home_score' => 3, 'away_score' => 1, 'date' => '2026-04-27 18:30:00', 'status' => 'completed'],
            ['round' => '3ª Rodada', 'home' => $teamIds[3], 'away' => $teamIds[5], 'home_score' => 0, 'away_score' => 0, 'date' => '2026-04-27 20:00:00', 'status' => 'completed'],
            ['round' => '3ª Rodada', 'home' => $teamIds[6], 'away' => $teamIds[4], 'home_score' => 2, 'away_score' => 1, 'date' => '2026-04-28 16:00:00', 'status' => 'completed'],
            ['round' => '4ª Rodada', 'home' => $teamIds[2], 'away' => $teamIds[1], 'home_score' => 1, 'away_score' => 2, 'date' => '2026-05-04 16:00:00', 'status' => 'completed'],
            ['round' => '4ª Rodada', 'home' => $teamIds[4], 'away' => $teamIds[3], 'home_score' => 0, 'away_score' => 1, 'date' => '2026-05-04 18:30:00', 'status' => 'completed'],
            ['round' => '4ª Rodada', 'home' => $teamIds[5], 'away' => $teamIds[0], 'home_score' => 1, 'away_score' => 1, 'date' => '2026-05-04 20:00:00', 'status' => 'completed'],
            ['round' => '4ª Rodada', 'home' => $teamIds[7], 'away' => $teamIds[6], 'home_score' => 2, 'away_score' => 3, 'date' => '2026-05-05 16:00:00', 'status' => 'completed'],
        ];

        foreach ($matchesData as $m) {
            Game::create([
                'championship_edition_id' => $edition->id,
                'home_team_id' => $m['home'],
                'away_team_id' => $m['away'],
                'round_name' => $m['round'],
                'match_date' => $m['date'],
                'home_score' => $m['home_score'],
                'away_score' => $m['away_score'],
                'status' => $m['status'],
            ]);
        }

        $this->command->info('Brasileirão 2026: times, classificação e partidas populados com sucesso!');
    }
}
