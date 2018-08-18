<?php

namespace Sheba\OnlinePayment;


use App\Models\Order;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Boolean;

interface PaymentGateway
{
    public function generateLink(Order $order, $isAdvancePayment);

    public function success(Request $request);

    public function formatTransactionData($gateway_response);
}