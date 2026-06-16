<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use App\Services\v2\ChampionshipService;
use App\Helpers\ReturnResponse;
use Illuminate\Http\Request;
use Exception;

class ChampionshipController extends Controller
{
    protected $service;

    public function __construct(ChampionshipService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $championships = \App\Models\Championship::all();
            return ReturnResponse::success("Campeonatos retornados com sucesso.", $championships);
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao retornar campeonatos.", [$e->getMessage()]);
        }
    }

    public function standings(string $slug, int $year)
    {
        try {
            $standings = $this->service->getStandings($slug, $year);
            return ReturnResponse::success("Classificação retornada com sucesso.", $standings);
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao retornar classificação.", [$e->getMessage()]);
        }
    }

    public function update(Request $request, string $slug)
    {
        try {
            $year = $request->input('year', date('Y'));
            
            // Mapeia o slug para a variável de ambiente correspondente
            $envVar = 'URL_' . strtoupper(str_replace('-', '_', $slug));
            $url = $request->input('url', env($envVar, env('URL_SITE_BRASILEIRAO'))); 

            $this->service->updateStandings($slug, $year, $url);
            return ReturnResponse::success("Dados de {$slug} atualizados com sucesso.");
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao atualizar dados.", [$e->getMessage()]);
        }
    }
}
