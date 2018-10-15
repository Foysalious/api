<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Sheba\Payment\PayCharge;
use Cache;

class SslController extends Controller
{
    public function validatePaycharge(Request $request)
    {
        try {
            if (empty($request->headers->get('referer'))) {
                $message = 'Referer not present in header';
                return api_response($request, null, 400, ['message' => $message]);
            };
            $paycharge = Cache::store('redis')->get("paycharge::$request->tran_id");
            if (!$paycharge) return redirect(config('sheba.front_url'));
            $paycharge = json_decode($paycharge);
            $pay_chargable = unserialize($paycharge->pay_chargable);
            $pay_charge = new PayCharge('online');
            $pay_charge->complete($request->tran_id);
            return redirect($pay_chargable->redirectUrl . '?invoice_id=' . $request->tran_id);
        } catch ( QueryException $e ) {
            app('sentry')->captureException($e);
            return redirect($pay_chargable->redirectUrl . '?invoice_id=' . $request->tran_id);
        } catch ( RequestException $e ) {
            app('sentry')->captureException($e);
            return redirect($pay_chargable->redirectUrl . '?invoice_id=' . $request->tran_id);
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


}