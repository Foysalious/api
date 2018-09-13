<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Sheba\PayCharge\PayCharge;
use Cache;

class SslController extends Controller
{
    public function validatePaycharge(Request $request)
    {
        try {
            if (empty($request->headers->get('referer'))) {
                $sentry = app('sentry');
                $sentry->user_context(['request' => $request->headers->all()]);
                $message = 'Referer not present in header';
                $sentry->captureException(new \Exception($message));
                return api_response($request, null, 400, ['message' => $message]);
            };
            $paycharge = Cache::store('redis')->get("paycharge::$request->tran_id");
            if (!$paycharge) return redirect(config('sheba.front_url'));
            $paycharge = json_decode($paycharge);
            $pay_chargable = unserialize($paycharge->pay_chargable);
            $pay_charge = new PayCharge('online');
            if ($response = $pay_charge->complete($request->tran_id)) {
                return redirect($response['redirect_url'] . '?invoice_id=' . $request->tran_id);
            } else {
                return api_response($request, null, 400, ['message' => $pay_charge->message]);
            }
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return redirect($pay_chargable->redirectUrl . '?invoice_id=' . $request->tran_id);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


}