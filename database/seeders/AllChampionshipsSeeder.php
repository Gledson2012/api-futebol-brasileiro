<?php

namespace Database\Seeders;

use App\Models\Championship;
use App\Models\ChampionshipEdition;
use App\Models\Game;
use App\Models\Standing;
use App\Models\Team;
use Illuminate\Database\Seeder;

class AllChampionshipsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLibertadores();
        $this->seedChampionsLeague();
        $this->seedWorldCup();
        $this->seedPremierLeague();
        $this->seedLaLiga();
        $this->seedSerieA();
        $this->seedBundesliga();
        $this->seedClubWorldCup();

        $this->command->info('Todos os campeonatos populados com sucesso!');
    }

    private function createEdition(string $slug, int $year, string $start, string $end): ?ChampionshipEdition
    {
        $championship = Championship::where('slug', $slug)->first();
        if (!$championship) return null;

        return ChampionshipEdition::firstOrCreate([
            'championship_id' => $championship->id,
            'year' => $year,
        ], [
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }

    private function createTeams(array $names): array
    {
        $ids = [];
        foreach ($names as $name) {
            $parts = explode('|', $name);
            $fullName = $parts[0];
            $shortName = $parts[1] ?? null;
            $team = Team::firstOrCreate(
                ['name' => $fullName],
                ['short_name' => $shortName, 'logo_url' => '']
            );
            $ids[] = $team->id;
        }
        return $ids;
    }

    private function createStandings(ChampionshipEdition $edition, array $data): void
    {
        foreach ($data as $i => $row) {
            Standing::updateOrCreate(
                [
                    'championship_edition_id' => $edition->id,
                    'team_id' => $row[0],
                ],
                [
                    'position' => $i + 1,
                    'points' => $row[1],
                    'played' => $row[2],
                    'won' => $row[3],
                    'drawn' => $row[4],
                    'lost' => $row[5],
                    'goals_for' => $row[6],
                    'goals_against' => $row[7],
                ]
            );
        }
    }

    private function createMatches(ChampionshipEdition $edition, array $matches): void
    {
        foreach ($matches as $m) {
            Game::create([
                'championship_edition_id' => $edition->id,
                'home_team_id' => $m[0],
                'away_team_id' => $m[1],
                'round_name' => $m[2],
                'match_date' => $m[3],
                'home_score' => $m[4],
                'away_score' => $m[5],
                'status' => 'completed',
            ]);
        }
    }

    private function seedLibertadores(): void
    {
        $edition = $this->createEdition('libertadores', 2026, '2026-02-04', '2026-11-29');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Flamengo|FLA', 'Palmeiras|PAL', 'River Plate|RIV', 'Boca Juniors|BOC',
            'São Paulo|SAO', 'Santos|SAN', 'Nacional|NAC', 'Peñarol|PEN',
            'Colo-Colo|COL', 'Independiente|IND', 'Estudiantes|EST', 'Racing|RAC',
            'Corinthians|COR', 'Internacional|INT', 'Grêmio|GRE', 'Athletico Paranaense|CAP',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 16, 6, 5, 1, 0, 14, 3],
            [$teams[1], 13, 6, 4, 1, 1, 10, 5],
            [$teams[2], 12, 6, 4, 0, 2, 11, 6],
            [$teams[3], 11, 6, 3, 2, 1, 8, 5],
            [$teams[4], 10, 6, 3, 1, 2, 9, 7],
            [$teams[5], 9, 6, 2, 3, 1, 7, 6],
            [$teams[6], 8, 6, 2, 2, 2, 6, 7],
            [$teams[7], 7, 6, 2, 1, 3, 5, 8],
            [$teams[8], 7, 6, 2, 1, 3, 6, 9],
            [$teams[9], 6, 6, 1, 3, 2, 5, 7],
            [$teams[10], 6, 6, 2, 0, 4, 7, 10],
            [$teams[11], 5, 6, 1, 2, 3, 4, 8],
            [$teams[12], 5, 6, 1, 2, 3, 5, 9],
            [$teams[13], 4, 6, 1, 1, 4, 4, 10],
            [$teams[14], 4, 6, 0, 4, 2, 3, 7],
            [$teams[15], 2, 6, 0, 2, 4, 3, 10],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[3], 'Grupo A', '2026-02-05 19:00:00', 2, 0],
            [$teams[1], $teams[2], 'Grupo A', '2026-02-05 21:00:00', 1, 0],
            [$teams[4], $teams[7], 'Grupo B', '2026-02-06 19:00:00', 2, 1],
            [$teams[5], $teams[6], 'Grupo B', '2026-02-06 21:00:00', 0, 0],
            [$teams[8], $teams[11], 'Grupo C', '2026-02-12 19:00:00', 2, 1],
            [$teams[9], $teams[10], 'Grupo C', '2026-02-12 21:00:00', 1, 1],
            [$teams[12], $teams[15], 'Grupo D', '2026-02-13 19:00:00', 1, 0],
            [$teams[13], $teams[14], 'Grupo D', '2026-02-13 21:00:00', 0, 1],
            [$teams[3], $teams[1], 'Grupo A', '2026-02-19 19:00:00', 1, 1],
            [$teams[2], $teams[0], 'Grupo A', '2026-02-19 21:00:00', 2, 3],
            [$teams[7], $teams[5], 'Grupo B', '2026-02-20 19:00:00', 0, 2],
            [$teams[6], $teams[4], 'Grupo B', '2026-02-20 21:00:00', 1, 0],
        ]);
    }

    private function seedChampionsLeague(): void
    {
        $edition = $this->createEdition('champions-league', 2026, '2026-07-01', '2027-06-01');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Real Madrid|RMA', 'Barcelona|BAR', 'Manchester City|MCI', 'Bayern Munich|BAY',
            'Paris Saint-Germain|PSG', 'Liverpool|LIV', 'Chelsea|CHE', 'Arsenal|ARS',
            'Inter Milan|INT', 'Milan|MIL', 'Juventus|JUV', 'Borussia Dortmund|BVB',
            'Atlético Madrid|ATM', 'Benfica|BEN', 'Porto|POR', 'Ajax|AJA',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 18, 6, 6, 0, 0, 18, 4],
            [$teams[1], 15, 6, 5, 0, 1, 16, 6],
            [$teams[2], 14, 6, 4, 2, 0, 14, 5],
            [$teams[3], 13, 6, 4, 1, 1, 12, 5],
            [$teams[4], 12, 6, 4, 0, 2, 13, 8],
            [$teams[5], 11, 6, 3, 2, 1, 10, 6],
            [$teams[6], 10, 6, 3, 1, 2, 9, 8],
            [$teams[7], 9, 6, 3, 0, 3, 8, 9],
            [$teams[8], 8, 6, 2, 2, 2, 7, 8],
            [$teams[9], 7, 6, 2, 1, 3, 6, 10],
            [$teams[10], 6, 6, 2, 0, 4, 5, 11],
            [$teams[11], 5, 6, 1, 2, 3, 6, 10],
            [$teams[12], 4, 6, 1, 1, 4, 5, 12],
            [$teams[13], 3, 6, 1, 0, 5, 4, 14],
            [$teams[14], 2, 6, 0, 2, 4, 3, 13],
            [$teams[15], 1, 6, 0, 1, 5, 2, 15],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[1], 'Grupo A', '2026-07-02 16:00:00', 3, 1],
            [$teams[2], $teams[3], 'Grupo A', '2026-07-02 16:00:00', 2, 2],
            [$teams[4], $teams[5], 'Grupo B', '2026-07-03 16:00:00', 1, 0],
            [$teams[6], $teams[7], 'Grupo B', '2026-07-03 16:00:00', 2, 1],
            [$teams[8], $teams[10], 'Grupo C', '2026-07-09 16:00:00', 1, 0],
            [$teams[9], $teams[11], 'Grupo C', '2026-07-09 16:00:00', 1, 1],
            [$teams[12], $teams[14], 'Grupo D', '2026-07-10 16:00:00', 2, 0],
            [$teams[13], $teams[15], 'Grupo D', '2026-07-10 16:00:00', 3, 0],
            [$teams[1], $teams[2], 'Grupo A', '2026-07-16 16:00:00', 1, 0],
            [$teams[3], $teams[0], 'Grupo A', '2026-07-16 16:00:00', 0, 2],
            [$teams[5], $teams[6], 'Grupo B', '2026-07-17 16:00:00', 1, 1],
            [$teams[7], $teams[4], 'Grupo B', '2026-07-17 16:00:00', 2, 3],
        ]);
    }

    private function seedWorldCup(): void
    {
        $edition = $this->createEdition('world-cup', 2026, '2026-06-11', '2026-07-12');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Brasil|BRA', 'Argentina|ARG', 'França|FRA', 'Inglaterra|ING',
            'Espanha|ESP', 'Alemanha|ALE', 'Portugal|POR', 'Holanda|HOL',
            'Itália|ITA', 'Uruguai|URU', 'Colômbia|COL', 'Bélgica|BEL',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 9, 3, 3, 0, 0, 8, 2],
            [$teams[1], 7, 3, 2, 1, 0, 6, 3],
            [$teams[2], 7, 3, 2, 1, 0, 5, 2],
            [$teams[3], 6, 3, 2, 0, 1, 7, 4],
            [$teams[4], 5, 3, 1, 2, 0, 4, 3],
            [$teams[5], 4, 3, 1, 1, 1, 5, 4],
            [$teams[6], 4, 3, 1, 1, 1, 3, 3],
            [$teams[7], 3, 3, 1, 0, 2, 4, 5],
            [$teams[8], 2, 3, 0, 2, 1, 2, 4],
            [$teams[9], 2, 3, 0, 2, 1, 1, 3],
            [$teams[10], 1, 3, 0, 1, 2, 2, 6],
            [$teams[11], 0, 3, 0, 1, 2, 1, 5],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[2], 'Grupo A', '2026-06-12 16:00:00', 2, 0],
            [$teams[1], $teams[3], 'Grupo A', '2026-06-12 16:00:00', 1, 1],
            [$teams[4], $teams[6], 'Grupo B', '2026-06-13 16:00:00', 1, 0],
            [$teams[5], $teams[7], 'Grupo B', '2026-06-13 16:00:00', 2, 1],
            [$teams[8], $teams[10], 'Grupo C', '2026-06-14 16:00:00', 1, 1],
            [$teams[9], $teams[11], 'Grupo C', '2026-06-14 16:00:00', 1, 0],
            [$teams[0], $teams[1], 'Grupo A', '2026-06-17 16:00:00', 3, 1],
            [$teams[2], $teams[3], 'Grupo A', '2026-06-17 16:00:00', 0, 2],
            [$teams[4], $teams[5], 'Grupo B', '2026-06-18 16:00:00', 1, 1],
            [$teams[6], $teams[7], 'Grupo B', '2026-06-18 16:00:00', 0, 0],
            [$teams[8], $teams[9], 'Grupo C', '2026-06-19 16:00:00', 0, 0],
            [$teams[10], $teams[11], 'Grupo C', '2026-06-19 16:00:00', 2, 1],
        ]);
    }

    private function seedPremierLeague(): void
    {
        $edition = $this->createEdition('premier-league', 2026, '2026-08-08', '2027-05-22');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Manchester City|MCI', 'Liverpool|LIV', 'Chelsea|CHE', 'Arsenal|ARS',
            'Manchester United|MUN', 'Tottenham|TOT', 'Newcastle|NEW', 'Aston Villa|AVL',
            'West Ham|WHU', 'Brighton|BRI', 'Wolverhampton|WOL', 'Crystal Palace|CRY',
            'Everton|EVE', 'Fulham|FUL', 'Brentford|BRE', 'Nottingham Forest|NOT',
            'Bournemouth|BOU', 'Leicester|LEI', 'Southampton|SOU', 'Ipswich|IPS',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 46, 18, 15, 1, 2, 48, 14],
            [$teams[1], 42, 18, 13, 3, 2, 40, 16],
            [$teams[2], 38, 18, 12, 2, 4, 36, 20],
            [$teams[3], 36, 18, 11, 3, 4, 34, 18],
            [$teams[4], 34, 18, 10, 4, 4, 30, 22],
            [$teams[5], 33, 18, 10, 3, 5, 35, 24],
            [$teams[6], 30, 18, 9, 3, 6, 28, 22],
            [$teams[7], 28, 18, 8, 4, 6, 27, 25],
            [$teams[8], 26, 18, 8, 2, 8, 24, 28],
            [$teams[9], 24, 18, 7, 3, 8, 22, 26],
            [$teams[10], 22, 18, 6, 4, 8, 20, 28],
            [$teams[11], 20, 18, 5, 5, 8, 18, 26],
            [$teams[12], 19, 18, 5, 4, 9, 16, 30],
            [$teams[13], 18, 18, 5, 3, 10, 20, 32],
            [$teams[14], 17, 18, 4, 5, 9, 18, 34],
            [$teams[15], 16, 18, 4, 4, 10, 16, 30],
            [$teams[16], 15, 18, 4, 3, 11, 14, 36],
            [$teams[17], 14, 18, 3, 5, 10, 12, 32],
            [$teams[18], 12, 18, 3, 3, 12, 10, 38],
            [$teams[19], 10, 18, 2, 4, 12, 8, 36],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[1], '1ª Rodada', '2026-08-09 16:00:00', 2, 1],
            [$teams[2], $teams[3], '1ª Rodada', '2026-08-09 16:00:00', 1, 0],
            [$teams[4], $teams[5], '1ª Rodada', '2026-08-10 16:00:00', 2, 2],
            [$teams[6], $teams[19], '1ª Rodada', '2026-08-10 16:00:00', 3, 0],
            [$teams[1], $teams[2], '2ª Rodada', '2026-08-16 16:00:00', 2, 0],
            [$teams[3], $teams[0], '2ª Rodada', '2026-08-16 16:00:00', 1, 1],
            [$teams[5], $teams[6], '2ª Rodada', '2026-08-17 16:00:00', 3, 1],
            [$teams[19], $teams[4], '2ª Rodada', '2026-08-17 16:00:00', 0, 4],
        ]);
    }

    private function seedLaLiga(): void
    {
        $edition = $this->createEdition('la-liga', 2026, '2026-08-15', '2027-05-23');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Real Madrid|RMA', 'Barcelona|BAR', 'Atlético Madrid|ATM', 'Sevilla|SEV',
            'Real Sociedad|RSO', 'Athletic Bilbao|ATH', 'Valencia|VAL', 'Villarreal|VIL',
            'Betis|BET', 'Osasuna|OSA', 'Getafe|GET', 'Girona|GIR',
            'Celta de Vigo|CEL', 'Rayo Vallecano|RAY', 'Mallorca|MAL', 'Las Palmas|LPA',
            'Alavés|ALA', 'Leganés|LEG', 'Valladolid|VLD', 'Espanyol|ESP',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 48, 18, 16, 0, 2, 50, 12],
            [$teams[1], 42, 18, 13, 3, 2, 44, 18],
            [$teams[2], 38, 18, 12, 2, 4, 36, 18],
            [$teams[3], 33, 18, 10, 3, 5, 30, 22],
            [$teams[4], 30, 18, 9, 3, 6, 28, 24],
            [$teams[5], 28, 18, 8, 4, 6, 26, 22],
            [$teams[6], 26, 18, 8, 2, 8, 24, 28],
            [$teams[7], 24, 18, 7, 3, 8, 22, 26],
            [$teams[8], 22, 18, 6, 4, 8, 20, 30],
            [$teams[9], 20, 18, 5, 5, 8, 18, 28],
            [$teams[10], 20, 18, 5, 5, 8, 16, 26],
            [$teams[11], 19, 18, 5, 4, 9, 20, 32],
            [$teams[12], 18, 18, 5, 3, 10, 18, 30],
            [$teams[13], 17, 18, 4, 5, 9, 14, 28],
            [$teams[14], 16, 18, 4, 4, 10, 16, 34],
            [$teams[15], 15, 18, 4, 3, 11, 12, 36],
            [$teams[16], 14, 18, 3, 5, 10, 10, 32],
            [$teams[17], 14, 18, 3, 5, 10, 8, 30],
            [$teams[18], 13, 18, 3, 4, 11, 10, 34],
            [$teams[19], 12, 18, 2, 6, 10, 8, 32],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[1], '1ª Rodada', '2026-08-16 16:00:00', 2, 1],
            [$teams[2], $teams[3], '1ª Rodada', '2026-08-16 16:00:00', 3, 0],
            [$teams[4], $teams[19], '1ª Rodada', '2026-08-17 16:00:00', 2, 0],
            [$teams[5], $teams[6], '1ª Rodada', '2026-08-17 16:00:00', 1, 1],
            [$teams[1], $teams[2], '2ª Rodada', '2026-08-23 16:00:00', 2, 2],
            [$teams[3], $teams[0], '2ª Rodada', '2026-08-23 16:00:00', 0, 3],
            [$teams[19], $teams[5], '2ª Rodada', '2026-08-24 16:00:00', 0, 2],
            [$teams[6], $teams[4], '2ª Rodada', '2026-08-24 16:00:00', 1, 2],
        ]);
    }

    private function seedSerieA(): void
    {
        $edition = $this->createEdition('serie-a-italy', 2026, '2026-08-22', '2027-05-30');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Inter Milan|INT', 'Milan|MIL', 'Juventus|JUV', 'Napoli|NAP',
            'Roma|ROM', 'Lazio|LAZ', 'Atalanta|ATA', 'Fiorentina|FIO',
            'Bologna|BOL', 'Torino|TOR', 'Udinese|UDI', 'Genoa|GEN',
            'Monza|MON', 'Lecce|LEC', 'Cagliari|CAG', 'Empoli|EMP',
            'Parma|PAR', 'Como|COM', 'Venezia|VEN', 'Verona|VER',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 44, 18, 14, 2, 2, 42, 14],
            [$teams[1], 40, 18, 12, 4, 2, 38, 16],
            [$teams[2], 38, 18, 12, 2, 4, 36, 18],
            [$teams[3], 35, 18, 11, 2, 5, 32, 20],
            [$teams[4], 30, 18, 9, 3, 6, 28, 24],
            [$teams[5], 28, 18, 8, 4, 6, 24, 22],
            [$teams[6], 26, 18, 8, 2, 8, 26, 28],
            [$teams[7], 24, 18, 7, 3, 8, 22, 26],
            [$teams[8], 22, 18, 6, 4, 8, 20, 28],
            [$teams[9], 20, 18, 5, 5, 8, 18, 26],
            [$teams[10], 18, 18, 5, 3, 10, 16, 30],
            [$teams[11], 17, 18, 4, 5, 9, 14, 28],
            [$teams[12], 16, 18, 4, 4, 10, 16, 32],
            [$teams[13], 15, 18, 4, 3, 11, 12, 30],
            [$teams[14], 14, 18, 3, 5, 10, 10, 34],
            [$teams[15], 12, 18, 3, 3, 12, 8, 36],
            [$teams[16], 11, 18, 2, 5, 11, 8, 34],
            [$teams[17], 10, 18, 2, 4, 12, 6, 36],
            [$teams[18], 8, 18, 1, 5, 12, 4, 38],
            [$teams[19], 6, 18, 0, 6, 12, 2, 40],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[1], '1ª Rodada', '2026-08-23 16:00:00', 1, 0],
            [$teams[2], $teams[3], '1ª Rodada', '2026-08-23 16:00:00', 3, 1],
            [$teams[4], $teams[5], '1ª Rodada', '2026-08-24 16:00:00', 2, 1],
            [$teams[6], $teams[19], '1ª Rodada', '2026-08-24 16:00:00', 3, 0],
            [$teams[1], $teams[2], '2ª Rodada', '2026-08-30 16:00:00', 0, 0],
            [$teams[3], $teams[0], '2ª Rodada', '2026-08-30 16:00:00', 1, 2],
            [$teams[5], $teams[6], '2ª Rodada', '2026-08-31 16:00:00', 2, 0],
            [$teams[19], $teams[4], '2ª Rodada', '2026-08-31 16:00:00', 0, 3],
        ]);
    }

    private function seedBundesliga(): void
    {
        $edition = $this->createEdition('bundesliga', 2026, '2026-08-15', '2027-05-17');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Bayern Munich|BAY', 'Borussia Dortmund|BVB', 'RB Leipzig|RBL', 'Bayer Leverkusen|LEV',
            'Eintracht Frankfurt|SGE', 'Stuttgart|STU', 'Wolfsburg|WOL', 'Borussia M\'gladbach|BMG',
            'Werder Bremen|BRE', 'Freiburg|FRE', 'Hoffenheim|HOF', 'Mainz|MAI',
            'Augsburg|AUG', 'Union Berlin|UNB', 'Heidenheim|HEI', 'St. Pauli|STP',
            'Holstein Kiel|KIE', 'Bochum|BOC',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 44, 17, 14, 2, 1, 48, 12],
            [$teams[1], 37, 17, 11, 4, 2, 36, 18],
            [$teams[2], 34, 17, 10, 4, 3, 32, 20],
            [$teams[3], 32, 17, 10, 2, 5, 30, 22],
            [$teams[4], 28, 17, 8, 4, 5, 26, 24],
            [$teams[5], 25, 17, 7, 4, 6, 24, 26],
            [$teams[6], 23, 17, 6, 5, 6, 22, 24],
            [$teams[7], 22, 17, 6, 4, 7, 20, 26],
            [$teams[8], 21, 17, 6, 3, 8, 18, 28],
            [$teams[9], 20, 17, 5, 5, 7, 16, 24],
            [$teams[10], 18, 17, 5, 3, 9, 14, 30],
            [$teams[11], 17, 17, 4, 5, 8, 12, 28],
            [$teams[12], 16, 17, 4, 4, 9, 10, 30],
            [$teams[13], 14, 17, 3, 5, 9, 8, 28],
            [$teams[14], 13, 17, 3, 4, 10, 8, 32],
            [$teams[15], 12, 17, 2, 6, 9, 6, 34],
            [$teams[16], 11, 17, 2, 5, 10, 6, 36],
            [$teams[17], 10, 17, 2, 4, 11, 4, 38],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[1], '1ª Rodada', '2026-08-16 16:00:00', 3, 0],
            [$teams[2], $teams[3], '1ª Rodada', '2026-08-16 16:00:00', 2, 1],
            [$teams[4], $teams[17], '1ª Rodada', '2026-08-17 16:00:00', 3, 0],
            [$teams[5], $teams[6], '1ª Rodada', '2026-08-17 16:00:00', 1, 1],
            [$teams[1], $teams[2], '2ª Rodada', '2026-08-23 16:00:00', 2, 2],
            [$teams[3], $teams[0], '2ª Rodada', '2026-08-23 16:00:00', 0, 1],
            [$teams[17], $teams[5], '2ª Rodada', '2026-08-24 16:00:00', 0, 2],
            [$teams[6], $teams[4], '2ª Rodada', '2026-08-24 16:00:00', 1, 0],
        ]);
    }

    private function seedClubWorldCup(): void
    {
        $edition = $this->createEdition('club-world-cup', 2026, '2026-06-15', '2026-07-04');
        if (!$edition) return;

        $teams = $this->createTeams([
            'Real Madrid|RMA', 'Flamengo|FLA', 'Manchester City|MCI', 'Al Hilal|HIL',
            'Auckland City|AUC', 'Wydad Casablanca|WYD', 'Monterrey|MTY',
        ]);

        $this->createStandings($edition, [
            [$teams[0], 12, 4, 4, 0, 0, 12, 3],
            [$teams[1], 9, 4, 3, 0, 1, 8, 4],
            [$teams[2], 7, 4, 2, 1, 1, 7, 5],
            [$teams[3], 5, 4, 1, 2, 1, 5, 6],
            [$teams[4], 3, 4, 1, 0, 3, 3, 8],
            [$teams[5], 2, 4, 0, 2, 2, 2, 6],
            [$teams[6], 1, 4, 0, 1, 3, 1, 7],
        ]);

        $this->createMatches($edition, [
            [$teams[0], $teams[2], 'Semifinal', '2026-06-28 16:00:00', 3, 1],
            [$teams[1], $teams[3], 'Semifinal', '2026-06-29 16:00:00', 2, 0],
            [$teams[4], $teams[5], '5º lugar', '2026-06-30 16:00:00', 1, 0],
            [$teams[2], $teams[3], '3º lugar', '2026-07-02 16:00:00', 2, 1],
            [$teams[0], $teams[1], 'Final', '2026-07-04 16:00:00', 2, 1],
        ]);
    }
}
