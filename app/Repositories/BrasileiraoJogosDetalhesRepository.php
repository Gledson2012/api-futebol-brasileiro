<?php

namespace App\Repositories;

use App\Models\BrasileiraoJogosDetalhes;
use App\Repositories\Contracts\BrasileiraoJogosDetalhesRepositoryInterface;
use Carbon\Carbon;

class BrasileiraoJogosDetalhesRepository implements BrasileiraoJogosDetalhesRepositoryInterface
{
    /** @var BrasileiraoJogosDetalhes $entity */
    protected $entity;

    /**
     * Define o Model utilizado neste repository.
     *
     * @param BrasileiraoJogosDetalhes $brasileiraoJogosDetalhes
     */
    public function __construct(BrasileiraoJogosDetalhes $brasileiraoJogosDetalhes)
    {
        $this->entity = $brasileiraoJogosDetalhes;
    }

    /**
     * Recupera detalhes de um jogo do campeonato brasileiro pelo código de referência.
     *
     * @param String $codigo_referencia
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function detalhes_do_jogo(string $codigo_referencia)
    {
        return $this->entity->query()
            ->where("codigo_referencia_jogo", $codigo_referencia)
            ->first();
    }
}
