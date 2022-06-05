<?php

namespace Sheba\Payment\Methods\Upay\Response;

use Sheba\Helpers\ArrayReflection;

class UpayValidateApiResponse extends ArrayReflection
{
    public $status;
    public $session_id;
    public $txn_id;
    public $date;
    public $invoice_id;
    public $amount;
    public $merchant_name;
    public function isSuccess(){
        return $this->status=='success';
    }
}