<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChampionshipEdition extends Model
{
    use HasFactory;

    protected $fillable = ['championship_id', 'year', 'start_date', 'end_date'];

    public function championship()
    {
        return $this->belongsTo(Championship::class);
    }

    public function matches()
    {
        return $this->hasMany(Game::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }
}
