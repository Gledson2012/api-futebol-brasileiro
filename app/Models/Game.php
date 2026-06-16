<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'championship_edition_id',
        'home_team_id',
        'away_team_id',
        'round_name',
        'match_date',
        'home_score',
        'away_score',
        'status',
        'statistics'
    ];

    protected $casts = [
        'statistics' => 'json',
        'match_date' => 'datetime'
    ];

    public function edition()
    {
        return $this->belongsTo(ChampionshipEdition::class, 'championship_edition_id');
    }

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
