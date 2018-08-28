<?php

namespace Sheba\OnlinePayment;


use App\Models\Order;
use App\Models\PartnerOrder;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Boolean;

interface PaymentGateway
{
    public function generateLink(PartnerOrder $order, $isAdvancePayment);

    public function success(Request $request);

    public function formatTransactionData($gateway_response);
}