<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class Cors2MiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $domains = [
            "http://localhost",
            "http://localhost:8080",
            "http://localhost:8081",
            "http://192.168.1.109:8080",
            "http://192.168.1.108:8080",
            "http://dev-sheba.xyz",
            "http://business.dev-sheba.xyz",
            "http://www.dev-sheba.xyz",
            "http://admin.dev-sheba.xyz",
            "https://admin.dev-sheba.xyz",
            "http://partners.dev-sheba.xyz",
            "http://accounts.dev-sheba.xyz",
            "http://api.sheba.test",
            "https://api.dev-sheba.xyz",
            "http://sheba.dev",
            null,
            "chrome-extension://fhbjgbiflinjbdggehcddcbncdddomop",
            "http://admin.sheba.test",
            "http://partners.sheba.test",
            "http://partners.sheba.new",
            "https://www.sheba.xyz",
            "https://admin.sheba.xyz",
            "https://partners.sheba.xyz",
            "http://admin.sheba.new",
            "http://accounts.sheba.test"
        ];
        // ALLOW OPTIONS METHOD
        $headers['Access-Control-Allow-Credentials'] = 'true';
        $headers['Access-Control-Allow-Methods'] = 'POST, GET, PUT, DELETE';
        $headers['Access-Control-Allow-Headers'] = 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With';
        $headers['Access-Control-Allow-Origin'] = $request->server('HTTP_ORIGIN');
        if (!in_array($request->server('HTTP_ORIGIN'), $domains)) {
            return response()->json(['message' => 'Unauthorized', 'code' => 401])->withHeaders($headers);
        }

        // ALLOW OPTIONS METHOD
        $headers['Access-Control-Allow-Methods'] = 'POST, GET, PUT, DELETE';
        $headers['Access-Control-Allow-Headers'] = 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With';
        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }
        return $response;
    }
}
