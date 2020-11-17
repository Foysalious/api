<?php namespace App\Http\Middleware\B2B;

use App\Models\PartnerOrder;
use Closure;

class OrderMiddleware
{
    public function handle($request, Closure $next)
    {
        $customer = $request->manager_member->profile->customer;
        /** @var PartnerOrder $partner_order */
        $partner_order = PartnerOrder::find((int)$request->order);
        $job = $partner_order->getActiveJob();
        if (!$job) {
            return api_response($request, null, 404, ["message" => "Order not found."]);
        }
        if ($partner_order->order->customer_id != $customer->id) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this order."]);
        }
        $request->merge(['job' => $job, 'partner_order' => $partner_order]);

        return $next($request);
    }
}
