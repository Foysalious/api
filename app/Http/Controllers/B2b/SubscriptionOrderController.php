<?php namespace App\Http\Controllers\B2b;

use Sheba\Payment\Adapters\Payable\SubscriptionOrderAdapter;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionOrder;
use Sheba\Payment\ShebaPayment;
use Illuminate\Http\Request;
use App\Models\Business;
use GuzzleHttp\Client;

class SubscriptionOrderController extends Controller
{

    public function index(Request $request)
    {
        try {
            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/subscriptions/order-lists?remember_token=$customer->remember_token";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['subscription_orders' => $response->subscription_orders_list]) : api_response($request, $response, $response->code);
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

    public function clearPayment($subscription_order, Request $request, ShebaPayment $sheba_payment)
    {
        try {
            $this->validate($request, ['payment_method' => 'required|string|in:bkash,wallet,cbl,online']);
            $payment_method = $request->payment_method;
            /** @var Business $business */
            $business = $request->business;
            $member = $request->manager_member;
            /** @var SubscriptionOrder $subscription_order */
            $subscription_order = SubscriptionOrder::find((int)$subscription_order);
            if ($payment_method == 'wallet' && $subscription_order->getTotalPrice() > $business->shebaCredit()) return api_response($request, null, 403, ['message' => 'You don\'t have sufficient credit.']);
            $order_adapter = new SubscriptionOrderAdapter();
            $payable = $order_adapter->setModelForPayable($subscription_order)->getPayable();
            $payment = $sheba_payment->setMethod($payment_method)->init($payable);
        } catch (\Throwable $e) {

        }
    }
}