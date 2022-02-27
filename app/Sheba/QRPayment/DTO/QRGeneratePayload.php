<?php

namespace App\Sheba\QRPayment\DTO;

use Sheba\Helpers\BasicGetter;

class QRGeneratePayload
{
    use BasicGetter;

    private $payable_type;
    private $type_id;
    private $amount;
    private $payer_id;
    private $payer_type;
    private $payment_method;

    public function __construct(array $payload)
    {
        $this->payable_type = $payload['payable_type'];
        $this->type_id = $payload['type_id'];
        $this->amount = $payload['amount'];
        $this->payer_id = $payload['payer_id'];
        $this->payer_type = $payload['payer_type'];
        $this->payment_method = $payload['payment_method'];
    }
}