<?php

namespace Sheba\Payment\Methods\Upay\Response;

use Sheba\Helpers\ArrayReflection;

class UpayInitiatePaymentResponse extends ArrayReflection
{
    public $session_id;
    public $txn_id;
    public $invoice_id;
    public $merchant_id;
    public $gateway_url;
}