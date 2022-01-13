<?php

namespace App\Http\Controllers\ResellerPayment;

use App\Http\Controllers\Controller;
use App\Sheba\ResellerPayment\PaymentGateway\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{

    public function getPaymentGatewayDetails(Request $request, PaymentGateway $paymentGateway)
    {
        $this->validate($request, [
            'key' => 'required|in:', implode(',', config('reseller_payment.available_payment_gateway_keys'))
        ]);
        $detail = $paymentGateway->setKey($request->key)->getDetails();
        return api_response($request, null, 200, ['data' => $detail]);
    }

}