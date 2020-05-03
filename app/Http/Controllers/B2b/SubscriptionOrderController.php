<?php namespace App\Http\Controllers\B2b;

use App\Models\Service;
use Carbon\Carbon;
use Sheba\Payment\Adapters\Payable\SubscriptionOrderAdapter;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionOrder;
use Sheba\Payment\ShebaPayment;
use Illuminate\Http\Request;
use App\Models\Business;
use GuzzleHttp\Client;
use Sheba\Reports\PdfHandler;

class SubscriptionOrderController extends Controller
{

    public function index(Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/subscriptions/order-lists?offset=$request->offset&limit=$request->limit&status=$request->status&remember_token=$customer->remember_token";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, [
                        'subscription_orders' => $response->subscription_orders_list,
                        'subscription_orders_count' => $response->subscription_order_count,
                    ]) : api_response($request, $response, $response->code);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($businesses, $order, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/subscriptions/$order/details?remember_token=$customer->remember_token";
                $client = new Client();
                $res = $client->request('GET', $url);

                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['subscription_order_details' => $response->subscription_order_details]) : api_response($request, $response, $response->code);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function clearPayment($business, $subscription_order, Request $request, ShebaPayment $sheba_payment)
    {
        $this->validate($request, ['payment_method' => 'required|string|in:bkash,wallet,cbl,online']);
        $payment_method = $request->payment_method;
        /** @var Business $business */
        $business = $request->business;
        $member = $request->manager_member;
        /** @var SubscriptionOrder $subscription_order */
        $subscription_order = SubscriptionOrder::find((int)$subscription_order);
        $subscription_order->calculate();
        if ($subscription_order->due <= 0) return api_response($request, null, 403, ['message' => 'Your order is already paid.']);
        if ($payment_method == 'wallet' && $subscription_order->due > $business->wallet) return api_response($request, null, 403, ['message' => 'You don\'t have sufficient credit.']);
        $order_adapter = new SubscriptionOrderAdapter();
        $payable = $order_adapter->setModelForPayable($subscription_order)->setUser($business)->getPayable();
        $payment = $sheba_payment->setMethod($payment_method)->init($payable);
        return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
    }

    public function orderInvoice($businesses, $order, Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;

            $subscription_order = SubscriptionOrder::find((int)$order);
            $partner = $subscription_order->partner;

            $service_details = json_decode($subscription_order->service_details);
            $service_details_breakdown = $service_details->breakdown['0'];

            $partner_orders = $subscription_order->orders->map(function ($order) {
                return $order->lastPartnerOrder();
            });

            $format_partner_orders = $partner_orders->map(function ($partner_order) use ($service_details_breakdown) {
                return [
                    'id' => $partner_order->order->code(),
                    'service_id' => $service_details_breakdown->id,
                    'service_name' => $service_details_breakdown->name,
                    'service_quantity' => $service_details_breakdown->quantity,
                    'service_unit_price' => $service_details_breakdown->unit_price,
                    'total' => $service_details_breakdown->quantity * $service_details_breakdown->unit_price,
                ];
            });
            $subscription_order_invoice = [
                "subscription_code" => $subscription_order->code(),
                "bill_pay_date" => Carbon::now()->format('d/m/y'),
                'partner' => [
                    "id" => $subscription_order->partner_id,
                    "name" => $partner->name,
                    "image" => $partner->logo,
                    "mobile" => $partner->getContactNumber(),
                    "email" => $partner->email ?: null,
                    "address" => $partner->address ?: null,
                ],
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->profile->name,
                    'mobile' => $customer->profile->mobile,
                ],
                "orders" => $format_partner_orders,
                'original_price' => $service_details->original_price,
                'delivery_charge' => $service_details->original_price,
                'discount' => $service_details->discount,
                'total_price' => $service_details->discounted_price,
                'subtotal' => $service_details->discounted_price,

            ];
            $handler = new PdfHandler();
            /*return $handler->setData($subscription_order_invoice)->setName('Subscription Order Invoice')->setViewFile('subscription_order_invoice')
                ->download();*/
            $link = $handler->setData($subscription_order_invoice)->setName('Subscription Order Invoice')->setViewFile('subscription_order_invoice')->save();
            return api_response($request, null, 200, ['message' => 'Successfully Download receipt', 'link' => $link]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
