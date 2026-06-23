<?php

namespace App\Observers;

use App\Models\Game;
use Illuminate\Support\Facades\Cache;

class GameObserver
{
    /**
     * Increment the cache version for matches of the specific edition.
     *
     * @param Game $game
     * @return void
     */
    private function incrementVersion(Game $game): void
    {
        if ($game->championship_edition_id) {
            $key = "championship_matches_version:{$game->championship_edition_id}";
            
            if (Cache::has($key)) {
                Cache::increment($key);
            } else {
                Cache::put($key, 1, now()->addDays(7));
            }
        }
    }

    /**
     * Handle the Game "saved" event.
     *
     * @param Game $game
     * @return void
     */
    public function saved(Game $game): void
    {
        $this->incrementVersion($game);
    }

    /**
     * Handle the Game "deleted" event.
     *
     * @param Game $game
     * @return void
     */
    public function deleted(Game $game): void
    {
        $this->incrementVersion($game);
    }
}
