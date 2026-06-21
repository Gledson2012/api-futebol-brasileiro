<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DeprecatedApi
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->header('X-API-Deprecated', 'true');
        $response->header('X-API-Sunset', 'v2');
        $response->header('X-API-Migration-Info', 'Migre para /api/v2/championships. Veja https://github.com/anomalyco/api-futebol-brasileiro');

        return $response;
    }
}
