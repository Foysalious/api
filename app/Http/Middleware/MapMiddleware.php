<?php namespace App\Http\Middleware;

use Closure;

class MapMiddleware
{
    public function handle($request, Closure $next)
    {
        $domains = [
            /** Customer Website */
            "http://dev-sheba.xyz",
            "http://www.dev-sheba.xyz",
            "https://www.dev-sheba.xyz",
            "https://www.sheba.xyz",
            /** Admin Portal */
            "http://admin.sheba.test",
            "http://admin.dev-sheba.xyz",
            "https://admin.dev-sheba.xyz",
            "https://admin.sheba.xyz",
            /** Business Portal */
            "http://business.dev-sheba.xyz",
            "http://b2b.sheba.test",
            "https://b2b.dev-sheba.xyz",
            "https://business.sheba.xyz",
            /** Partner Portal */
            "http://partners.dev-sheba.xyz",
            "https://partners.dev-sheba.xyz",
            "http://partners.sheba.test",
            "https://partners.sheba.xyz",
            /** API */
            "http://api.sheba.test",
            "https://api.dev-sheba.xyz",
            "https://api.sheba.xyz",
            /** Accounts */
            "http://accounts.dev-sheba.xyz",
            "http://accounts.sheba.test",
            "https://accounts.sheba.xyz",
            /** Bondhu website */
            "http://bondhu.dev-sheba.xyz",
            "https://bondhu.dev-sheba.xyz",
            "http://bondhu.sheba.xyz",
            "https://bondhu.sheba.xyz",
            /** Others */
            "http://transport.dev-sheba.xyz",
            "http://movie.dev-sheba.xyz",
            "https://topup.dev-sheba.xyz",
            "https://pl.dev-sheba.xyz",
            "https://pl.sheba.xyz",
            "https://topup.sheba.xyz",
            "https://transport.sheba.xyz",
            "https://movie.sheba.xyz"
        ];
        if (!in_array($request->server('HTTP_ORIGIN'), $domains) || !in_array($request->ip(), ['127.0.0.1'])) return response()->json(['message' => 'Unauthorized', 'code' => 401]);
        return $next($request);
    }
}