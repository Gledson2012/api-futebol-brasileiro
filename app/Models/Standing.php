<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_edition_id',
        'team_id',
        'position',
        'points',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against'
    ];

    public function edition()
    {
        return $this->belongsTo(ChampionshipEdition::class, 'championship_edition_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
