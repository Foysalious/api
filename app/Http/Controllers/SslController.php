<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Cache;
use Sheba\Payment\ShebaPayment;

class SslController extends Controller
{
    public function validatePaycharge(Request $request)
    {
        try {
            $payment = Payment::where('transaction_id', $request->tran_id)
                ->where('status', '<>', 'validation_failed')
                ->first();
            if (!$payment) return redirect(config('sheba.front_url'));
            $sheba_payment = new ShebaPayment('online');
            $sheba_payment->complete($payment);
            $payable = $payment->payable;
            return redirect($payable->success_url . '?invoice_id=' . $request->tran_id);
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return redirect($payable->success_url . '?invoice_id=' . $request->tran_id);
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return redirect($payable->success_url . '?invoice_id=' . $request->tran_id);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


}