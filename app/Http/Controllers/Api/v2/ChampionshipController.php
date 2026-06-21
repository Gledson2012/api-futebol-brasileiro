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
            
            $urls = config('scrapers.urls', []);
            $url = $request->input('url', $urls[$slug] ?? env('URL_SITE_BRASILEIRAO'));

            if (!$url) {
                return ReturnResponse::warning("URL não configurada para o campeonato: {$slug}. Defina a URL no request ou no config/scrapers.php.");
            }

            $this->service->updateStandings($slug, $year, $url);
            return ReturnResponse::success("Dados de {$slug} atualizados com sucesso.");
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao atualizar dados.", [$e->getMessage()]);
        }
    }

    public function matches(Request $request, string $slug)
    {
        try {
            $year = $request->input('year', date('Y'));
            $filters = $request->only(['team', 'round', 'status']);

            $matches = $this->service->getMatches($slug, $year, $filters);
            return ReturnResponse::success("Partidas retornadas com sucesso.", $matches);
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao retornar partidas.", [$e->getMessage()]);
        }
    }

    public function updateMatches(Request $request, string $slug)
    {
        try {
            $year = $request->input('year', date('Y'));

            $apiUrls = config('scrapers.api_urls', []);
            $url = $request->input('url', $apiUrls[$slug] ?? null);

            if (!$url) {
                return ReturnResponse::warning("URL da API não configurada para o campeonato: {$slug}. Defina a URL no request ou no config/scrapers.php.");
            }

            $count = $this->service->updateMatches($slug, $year, $url);
            return ReturnResponse::success("{$count} partidas de {$slug} atualizadas com sucesso.");
        } catch (Exception $e) {
            return ReturnResponse::error("Erro ao atualizar partidas.", [$e->getMessage()]);
        }
    }
}
