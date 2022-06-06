<?php

namespace App\Sheba\QRPayment;

use Sheba\Dal\QRPayable\Model as QRPayable;
use Sheba\Payment\Adapters\Payable\PayableAdapter;

interface QrPayableAdapter extends PayableAdapter
{
    public function getQrPayable(): QRPayable;
}