<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class Cors2MiddleWare {
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
            "http://localhost:8080",
            "http://dev-sheba.xyz",
            "http://admin.dev-sheba.xyz",
            //"http://sheba.dev",
        ];


        // ALLOW OPTIONS METHOD
        $headers['Access-Control-Allow-Methods'] = 'POST, GET, PUT, DELETE';
        $headers['Access-Control-Allow-Headers'] = 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With';
        $headers['Access-Control-Allow-Origin'] = $request->server('HTTP_ORIGIN');

        if(!in_array($request->server('HTTP_ORIGIN'), $domains)) {
            return response()
                ->json(['message' => 'Unauthorized', 'code' => 401])
                ->withHeader($headers);
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
