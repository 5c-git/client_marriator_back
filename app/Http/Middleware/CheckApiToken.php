<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckApiToken
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
        if ($request->api_token && User::checkToken($request->api_token)) {
            return $next($request);
        } else {
            return response()->json([
                'status' => 'error',
                'massage' => 'Invalid api token'
            ]);
        }

    }
}
