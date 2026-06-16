<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Championship extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'type'];

    public function editions()
    {
        return $this->hasMany(ChampionshipEdition::class);
    }
}
