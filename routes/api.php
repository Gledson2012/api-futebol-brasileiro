<?php

use App\Http\Controllers\Api\v1\Brasileirao\ApiBrasileiraoController;
use App\Http\Controllers\Api\v2\ChampionshipController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API v2 - Generic Architecture
Route::prefix("v2")->group(function () {
    Route::prefix("championships")->group(function () {
        Route::get("{slug}/standings/{year}", [ChampionshipController::class, 'standings']);
        Route::post("{slug}/update", [ChampionshipController::class, 'update']);
        // Future: matches, details, etc.
    });
});

// API v1 - Legacy Brasileirao
Route::namespace("v1")->prefix("campeonato")->group(function () {
    Route::namespace("brasileirao")->prefix("brasileiro")->group(function () {
        Route::prefix("tabela")->group(function () {
            Route::get("/", [ApiBrasileiraoController::class, 'tabela']);
            Route::get("por-rodada/{rodada}/{temporada}", [ApiBrasileiraoController::class, 'tabelaPorRodada']);
        });

        Route::prefix("jogos")->group(function () {
            Route::get("/", [ApiBrasileiraoController::class, 'jogos']);
            Route::get("por-rodada/{rodada}/{temporada}", [ApiBrasileiraoController::class, 'jogosPorRodada']);
            Route::get("por-time/{nomeTime}", [ApiBrasileiraoController::class, 'jogosPorTime']);
            Route::get("detalhes/{timesJogo}/{idReferencia}", [ApiBrasileiraoController::class, 'jogoDetalhes']);
        });
    });
});

Route::fallback(function () {
    return response()->json([
        "status"  => false,
        "message" => env("APP_NAME"),
        "data"    => "Página não encontrada."
    ], 404);
});
