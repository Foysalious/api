<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redis;
use Cache;
use Sheba\Payment\ShebaPayment;
use Sheba\Settings\Payment\PaymentSetting;

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

    public function validatePayment(Request $request)
    {
        try {
            $this->validate($request, ['paymentID' => 'required']);
            $payment = Payment::where('transaction_id', $request->paymentID)->valid()->first();
            if (!$payment) return api_response($request, null, 404, ['message' => 'Valid Payment not found.']);
            $sheba_payment = new ShebaPayment('bkash');
            $payment = $sheba_payment->complete($payment);
            $redirect_url = $payment->payable->success_url . '?invoice_id=' . $request->paymentID;
            if ($payment->isComplete()) {
                return api_response($request, 1, 200, ['payment' => array('redirect_url' => $redirect_url)]);
            } elseif ($payment->isFailed()) {
                return api_response($request, null, 400, [
                    'message' => 'Your payment has been failed due to ' . json_decode($payment->transaction_details)->errorMessage,
                    'payment' => array('redirect_url' => $redirect_url)
                ]);
            } elseif ($payment->isPassed()) {
                return api_response($request, 1, 400, [
                    'message' => 'Your payment has been received but there was a system error. It will take some time to update your transaction. Call 16516 for support.',
                    'payment' => array('redirect_url' => $redirect_url)
                ]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function validateAgreement(Request $request, PaymentSetting $paymentSetting)
    {
        try {
            $paymentSetting->setMethod('bkash')->save($request->paymentID);
            return api_response($request, 1, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPaymentInfo($paymentID, Request $request)
    {
        try {
            $payment = Payment::where('transaction_id', $paymentID)->valid()->first();
            return $payment ? api_response($request, $payment, 200, ['data' => json_decode($payment->transaction_details)]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function token($paymentID, Request $request)
    {
        try {
            $payment = Payment::where('transaction_id', $request->paymentID)->valid()->first();
            (new ShebaPayment('bkash'))->token($payment);
            dd($payment);
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}