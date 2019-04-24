<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\Member;
use App\Models\Partner;
use App\Models\Resource;
use Closure;

class BusinessAuthMiddleware
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
        if ($request->has('remember_token')) {
            $business_member = Member::where('remember_token', $request->input('remember_token'))->first();

            $business = Business::find($request->business);
            if ($business_member && $business) {
                if ($business_member->isManager($business)) {
                    $request->merge(['business_member' => $business_member, 'business' => $business]);
                    return $next($request);
                } else {
                    return api_response($request, null, 403, ["message" => "Forbidden. You're not a manager of this partner."]);
                }
            } else {
                return api_response($request, null, 404, ["message" => 'Partner or Resource not found.']);
            }
        } else {
            return api_response($request, null, 400, ["message" => "Authentication token is missing from the request."]);
        }
    }
}
