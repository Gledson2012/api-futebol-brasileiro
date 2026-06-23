<?php

namespace App\Observers;

use App\Models\Standing;
use Illuminate\Support\Facades\Cache;

class StandingObserver
{
    /**
     * Clear the cache for the specific championship edition.
     *
     * @param Standing $standing
     * @return void
     */
    private function clearCache(Standing $standing): void
    {
        $edition = $standing->edition;
        if ($edition && $edition->championship) {
            $slug = $edition->championship->slug;
            $year = $edition->year;
            Cache::forget("championship_standings:{$slug}:{$year}");
        }
    }

    /**
     * Handle the Standing "saved" event.
     *
     * @param Standing $standing
     * @return void
     */
    public function saved(Standing $standing): void
    {
        $this->clearCache($standing);
    }

    /**
     * Handle the Standing "deleted" event.
     *
     * @param Standing $standing
     * @return void
     */
    public function deleted(Standing $standing): void
    {
        $this->clearCache($standing);
    }
}
