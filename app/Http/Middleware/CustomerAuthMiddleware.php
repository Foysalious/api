<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use JWTAuth;

class CustomerAuthMiddleware
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

        if ($request->filled('remember_token')) {
            $customer = Customer::where('remember_token', $request->input('remember_token'))->first();
            if ($customer) {
                if ($customer->id == $request->customer) {
                    $request->merge(['customer' => $customer]);
                    return $next($request);
                } else {
                    return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
                }
            } else {
                return api_response($request, null, 404, ["message" => "User not found."]);
            }
        } else {
            return api_response($request, null, 400, ["message" => "Authentication token is missing from the request."]);
        }
    }
}

