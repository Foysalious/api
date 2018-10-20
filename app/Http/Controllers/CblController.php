<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sheba\Payment\PayChargable;
use Sheba\Payment\ShebaPayment;
use Cache;


class CblController extends Controller
{
    public function validateCblPGR(Request $request)
    {
        /** @var PayChargable $pay_chargable */
        $pay_chargable = null;
        $invoice = '';
        try {
            $this->validate($request, [
                'xmlmsg' => 'required|string',
            ]);

            $xml = simplexml_load_string($request->xmlmsg);
            $invoice = "SHEBA_CBL_" . $xml->OrderID . '_' . $xml->SessionID;
            $pay_charge = Cache::store('redis')->get("paycharge::$invoice");
            if (!$pay_charge) return redirect(config('sheba.front_url'));
            $pay_charge = json_decode($pay_charge);
            $pay_chargable = unserialize($pay_charge->pay_chargable);
            $pay_charge = new ShebaPayment('cbl');
            $pay_charge->complete($invoice);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }

        return redirect($pay_chargable->redirectUrl . '?invoice_id=' . $invoice);
    }
}