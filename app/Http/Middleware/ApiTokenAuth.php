<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token || $token !== config('api.update_token', env('API_UPDATE_TOKEN'))) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized. Provide a valid Bearer token.',
                'data' => null,
                'count' => 0,
            ], 401);
        }

        return $next($request);
    }
}
