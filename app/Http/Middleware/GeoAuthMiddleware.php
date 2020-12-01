<?php namespace App\Http\Middleware;

use Closure;

class GeoAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($request->header('app-id') == config('geo.app_id') && $request->header('app_secret') == config('geo.app_secret')
            || $this->fromShebaWeb($request->server('HTTP_ORIGIN'))
        ) return $next($request);
        else return response()->json(['code' => 401, 'message' => 'Unauthorized']);
    }

    private function fromShebaWeb($origin)
    {
        return in_array($origin, [config('sheba.front_url'), 'http://localhost:3333', 'http://localhost:3337']);
    }
}