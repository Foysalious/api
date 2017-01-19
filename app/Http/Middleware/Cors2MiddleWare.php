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
            "http://192.168.1.109:8080",
            "http://192.168.1.108:8080",
            "http://dev-sheba.xyz",
            "http://admin.dev-sheba.xyz",
            "http://sheba.dev",
            null,
            "chrome-extension://fhbjgbiflinjbdggehcddcbncdddomop",
            "http://admin.sheba.dev",
            "http://sheba.xyz",
            "http://www.sheba.xyz",
            "http://admin.sheba.xyz",
            "http://admin.sheba.new"
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
