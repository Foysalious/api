<?php namespace App\Http\Middleware;

use Closure;

class GeoAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($request->header('app-id') == config('geo.app_id') && $request->header('app_secret') == config('geo.app_secret')) return $next($request);
        else return response()->json(['code' => 401, 'message' => 'Unauthorized']);
    }
}