<?php

namespace App\Http\Middleware;

use App\Models\PartnerOrder;
use Closure;

class PartnerOrderAuthMiddleware
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
        $partner = $request->partner;
        $partner_order = PartnerOrder::find($request->order);
        if (!$partner_order) {
            return api_response($request, null, 404, ["Order not found."]);
        }
        if ($partner_order->partner->id != $partner->id) {
            return api_response($request, null, 403, ["You're not authorized to access this order."]);
        }
        $request->merge(['partner_order' => $partner_order]);
        return $next($request);
    }
}
