<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class AdminAuthMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        If ($request->has('remember_token'))
        {
            $user = User::where('remember_token', $request->get('remember_token'))->first();
            //remember_token is valid for a customer
            if ($user)
            {
                return $next($request);
            }
            else
            {
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
            }
        }
    }
}
