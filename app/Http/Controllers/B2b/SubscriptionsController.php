<?php namespace App\Http\Controllers\B2b;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionsController
{
    public function index(Request $request)
    {
        try {
            /*$customer = $request->manager_member->profile->customer;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/orders?remember_token=$customer->remember_token&for=business";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['orders' => $response->orders]) : api_response($request, $response, $response->code);
                }
            } else {
                return api_response($request, null, 404);
            }*/
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

    public function show($order, Request $request)
    {
        try {
            $customer = $request->manager_member->profile->customer;
            $partner_order = $request->partner_order;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/orders/$partner_order->id?remember_token=$customer->remember_token";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['order' => $response->orders]) : api_response($request, $response, $response->code);
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
}