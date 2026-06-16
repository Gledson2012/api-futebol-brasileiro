<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'short_name', 'logo_url'];

    public function matchesAsHome()
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    public function matchesAsAway()
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }
}
