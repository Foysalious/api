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
        If ($request->has('remember_token')) {
            $customer = Customer::where('remember_token', $request->input('remember_token'))->first();
            //remember_token is valid for a customer
            if ($customer) {
                if ($customer->id == $request->customer) {
                    return $next($request);
                } else {
                    return response()->json(['msg' => 'unauthorized', 'code' => 409]);
                }
            }
        }
        return response()->json(['msg' => 'unauthorized', 'code' => 409]);
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
            if ($user->id != $request->customer) {
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
    }
}

