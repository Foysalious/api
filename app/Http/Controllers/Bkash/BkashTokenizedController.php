<?php


namespace App\Http\Controllers\Bkash;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\ShebaPayment;
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
            $key = 'order_' . $request->paymentID;
            $job = Redis::get($key);
            $payment = null;
            if ($job) {
                $job = Job::find((json_decode($job))->job_id);
                $order_adapter = new OrderAdapter($job->partnerOrder);
                $payment = (new ShebaPayment('bkash'))->init($order_adapter->getPayable());
                Redis::del($key);
            }
            return api_response($request, 1, 200, ['payment' => $payment ? $payment->getFormattedPayment() : null]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}