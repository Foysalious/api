<?php


namespace App\Http\Controllers\Bkash;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Settings\Payment\PaymentSetting;

class BkashTokenizedController extends Controller
{

    public function validatePayment(Request $request)
    {

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
}