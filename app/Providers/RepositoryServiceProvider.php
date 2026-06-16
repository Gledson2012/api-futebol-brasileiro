<?php

namespace App\Providers;

use App\Repositories\BrasileiraoJogosDetalhesRepository;
use App\Repositories\BrasileiraoJogosRepository;
use App\Repositories\BrasileiraoRepository;
use App\Repositories\Contracts\BrasileiraoJogosDetalhesRepositoryInterface;
use App\Repositories\Contracts\BrasileiraoJogosRepositoryInterface;
use App\Repositories\Contracts\BrasileiraoRepositoryInterface;
use App\Repositories\Contracts\ChampionshipRepositoryInterface;
use App\Repositories\ChampionshipRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /** Championship Repository v2 */
        $this->app->bind(ChampionshipRepositoryInterface::class, ChampionshipRepository::class);

        /** Brasileirao Repository */
        $this->app->bind(BrasileiraoRepositoryInterface::class, BrasileiraoRepository::class);

        /** Brasileirao Jogos Repository */
        $this->app->bind(BrasileiraoJogosRepositoryInterface::class, BrasileiraoJogosRepository::class);

        /** Brasileirao Jogos Detalhes Repository */
        $this->app->bind(BrasileiraoJogosDetalhesRepositoryInterface::class, BrasileiraoJogosDetalhesRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
