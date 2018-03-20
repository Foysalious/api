<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleWare {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [];
        $domains = [
            "http://localhost:8080",
            "http://dev-sheba.xyz",
            "http://admin.dev-sheba.xyz",
            "http://admin.sheba.test"
        ];
        if(in_array($request->server('HTTP_ORIGIN'), $domains)) {
            return Response::json($domains);
            $headers['Access-Control-Allow-Origin'] = $request->server('HTTP_ORIGIN');
        } else {
            return Response::json(['Unauthorized', 401]);
        }

        // ALLOW OPTIONS METHOD
        $headers['Access-Control-Allow-Methods'] = 'POST, GET, OPTIONS, PUT, DELETE';
        $headers['Access-Control-Allow-Headers'] = 'Content-Type, X-Auth-Token, Origin, Authorization, X-Requested-With';

        if ($request->getMethod() == "OPTIONS")
        {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return Response::make('OK', 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value)
        {
            $response->header($key, $value);
        }
        return $response;
    }
}
