<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class CustomerSubscriptionController extends Controller
{
    public function index(Request $request, $partner)
    {
        try {
            $partner = $request->partner;
            $subscription_orders_list = collect([]);
            $subscription_orders = SubscriptionOrder::where('partner_id', (int)$partner->id)->get();
            foreach ($subscription_orders as $subscription_order) {
                $served_orders = $subscription_order->orders->map(function ($order) {
                    return $order->partnerOrders->where('cancelled_at', null)->filter(function ($partner_order) {
                        return $partner_order->closed_and_paid_at != null;
                    });
                })->flatten()->count();

                #$schedules = collect(json_decode($subscription_order->schedules));

                $service_details = json_decode($subscription_order->service_details);
                $service_details_breakdown = $service_details->breakdown['0'];
                $service = Service::find((int)$service_details_breakdown->id);

                $orders_list = [
                    'subscription_order_id' => $subscription_order->id,
                    "service_name" => $service->name,
                    "app_thumb" => $service->app_thumb,
                    "billing_cycle" => $subscription_order->billing_cycle,
                    "subscription_period" => Carbon::parse($subscription_order->billing_cycle_start)->format('M j') . ' - ' . Carbon::parse($subscription_order->billing_cycle_end)->format('M j'),
                    "completed_orders" => $served_orders . '/' . $subscription_order->orders->count(),
                    "is_active" => Carbon::parse($subscription_order->billing_cycle_end) >= Carbon::today() ? 1 : 0,
                    "partner" =>
                        [
                            "id" => $subscription_order->partner_id,
                            "name" => $service_details->name,
                            "mobile" => $subscription_order->partner->mobile,
                            "logo" => $service_details->logo
                        ]
                ];
                $subscription_orders_list->push($orders_list);
            }
            return api_response($request, $subscription_orders_list, 200, ['subscription_orders_list' => $subscription_orders_list]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $partner, $subscription)
    {
        try {
            $partner = $request->partner;
            $subscription_order = SubscriptionOrder::find((int)$subscription);
            #dd($subscription_order);
            $partner_orders = $subscription_order->orders->map(function ($order) {
                return $order->lastPartnerOrder();
            });
            #dd($partner_orders);
            $partner_orders = $partner_orders->map(function ($partner_order) {
                return [
                    'id' => $partner_order->order->code(),
                    'is_completed' => $partner_order->closed_and_paid_at ? $partner_order->closed_and_paid_at->format('M-j a') : null,
                    'cancelled_at' => $partner_order->cancelled_at ? Carbon::parse($partner_order->cancelled_at)->format('M-j h:i a') : null
                ];
            });

            $served_orders = $partner_orders->filter(function ($partner_order) {
                return $partner_order['is_completed'] != null;
            });

            $service_details = json_decode($subscription_order->service_details);
            $service_details_breakdown = $service_details->breakdown['0'];
            $service = Service::find((int)$service_details_breakdown->id);
            $schedules = collect(json_decode($subscription_order->schedules));

            $subscription_order_details = [
                'service_id' => $service->id,
                "service_name" => $service->name,
                "app_thumb" => $service->app_thumb,
                'quantity' => (double)$service_details_breakdown->quantity,
                "partner_id" => $subscription_order->partner_id,
                "partner_name" => $service_details->name,
                "logo" => $service_details->logo,

                'customer_name' => $subscription_order->customer->profile->name,
                'customer_mobile' => $subscription_order->customer->profile->mobile,
                'address' => $subscription_order->deliveryAddress->address,
                'location_name' => $subscription_order->location->name,

                "billing_cycle" => $subscription_order->billing_cycle,
                "subscription_period" => Carbon::parse($subscription_order->billing_cycle_start)->format('M j') . ' - ' . Carbon::parse($subscription_order->billing_cycle_end)->format('M j'),
                "total_orders" => $subscription_order->orders->count(),
                "completed_orders" => $served_orders->count(),
                "preferred_time" => $schedules->first()->time,
                "days_left" => Carbon::today()->diffInDays(Carbon::parse($subscription_order->billing_cycle_end)),
                'original_price' => $service_details->original_price,
                'discount' => $service_details->discount,
                'total_price' => $service_details->discounted_price,
                "paid_on" => !empty($subscription_order->paid_at) ? Carbon::parse($subscription_order->paid_at)->format('M-j, Y') : null,
                "orders" => $partner_orders
            ];

            return api_response($request, $subscription_order_details, 200, ['subscription_order_details' => $subscription_order_details]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function bulkAccept(Request $request, $partner, SubscriptionOrder $subscription)
    {
        try {
            $form_data = [
                'remember_token' => $request->remember_token,
            ];
            $url = env('SHEBA_BACKEND_URL') . "/api/bulk-accept-subscription-orders/$subscription->id";
            $client = new Client();
            $response = $client->request('POST', $url, ['form_params' => $form_data]);

            if ($response = json_decode($response->getBody())) {
                return api_response($request, null, 200, ['message' => $response->msg]);
            }
            return api_response($request, null, 500);
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
