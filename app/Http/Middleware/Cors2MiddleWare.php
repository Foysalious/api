<?php

namespace App\Http\Middleware;

use Closure;

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
        ];
        if(in_array($request->server('HTTP_ORIGIN'), $domains)) {
            return Response::json($domains);
        } else {
            return Response::json(['Unauthorized', 401]);
        }
    }
}
