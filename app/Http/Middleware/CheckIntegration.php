<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIntegration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $apiToken = $request->header('Authorization');

        $apiToken = str_replace("Bearer ", "", $apiToken);

        if (!is_string($apiToken) || strlen($apiToken) < 40 || $apiToken != env('ONE_C_TOKEN')) {
            return response()->json([
                'errors' => 'Check credentials (1)',
            ], 403);
        }

        return $next($request);

    }
}
