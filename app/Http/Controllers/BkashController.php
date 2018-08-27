<?php

namespace App\Http\Controllers;

use App\Models\PartnerOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Redis;
use Sheba\OnlinePayment\Bkash;
use Sheba\OnlinePayment\Payment;

class BkashController extends Controller
{

    public function create($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'job' => 'required',
                'isAdvanced' => 'required|in:0,1'
            ]);
            $job = $request->job;
            $payment = new Payment($job->partnerOrder->order, new Bkash());
            $result = [];
            $query = parse_url($payment->generateLink((int)$request->isAdvanced))['query'];
            parse_str($query, $result);
            $key_name = $result['paymentID'];
            $payment_info = Redis::get("$key_name");
            $payment_info = json_decode($payment_info);
            return api_response($request, $result, 200, ['data' => $payment_info]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function execute(Request $request)
    {
        try {
            $payment_info = Redis::get("$request->paymentID");
            $payment_info = json_decode($payment_info);
            $partnerOrder = PartnerOrder::find((int)$payment_info->partner_order_id);
            $payment = new Payment($partnerOrder->order, new Bkash());
            if ($payment->success($request)) {
                return api_response($request, 1, 200);
            } else {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPaymentInfo($paymentID, Request $request)
    {
        try {
            $data = Redis::get("$paymentID");
            return $data ? api_response($request, $data, 200, ['data' => json_decode($data)]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}