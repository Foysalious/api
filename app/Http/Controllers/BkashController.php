<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Redis;
use Sheba\OnlinePayment\Bkash;
use Sheba\OnlinePayment\Payment;
use Cache;
use Sheba\PayCharge\PayCharge;

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
            $payment = new Payment($job->partnerOrder, new Bkash());
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

    public function validatePaycharge(Request $request)
    {
        try {
            $this->validate($request, ['paymentID' => 'required']);
            $paycharge = Cache::store('redis')->get("paycharge::$request->paymentID");
            if (!$paycharge) return redirect(config('sheba.front_url'));
            $paycharge = json_decode($paycharge);
            $pay_chargable = unserialize($paycharge->pay_chargable);
            $pay_charge = new PayCharge('bkash');
            if ($response = $pay_charge->complete($request->paymentID)) {
                return api_response($request, 1, 200, ['payment' => array('redirect_url' => $response['redirect_url'] . '?invoice_id=' . $request->paymentID)]);
            } else {
                return api_response($request, null, 400, ['message' => $pay_charge->message, 'payment' => array('redirect_url' => $response['redirect_url'] . '?invoice_id=' . $request->paymentID)]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message, 'payment' => array('redirect_url' => $response['redirect_url'] . '?invoice_id=' . $request->paymentID)]);
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 200, ['message' => 'Your payment has been received but there was a system error. It will take some time to update your order. Call 16516 for support.', 'payment' => array('redirect_url' => $response['redirect_url'] . '?invoice_id=' . $request->paymentID)]);
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 200, ['message' => 'Your payment has been received but there was a system error. It will take some time to update your order. Call 16516 for support.', 'payment' => array('redirect_url' => $response['redirect_url'] . '?invoice_id=' . $request->paymentID)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPaymentInfo($paymentID, Request $request)
    {
        try {
            $data = Cache::store('redis')->get("paycharge::$paymentID");
            return $data ? api_response($request, $data, 200, ['data' => json_decode($data)->method_info]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}