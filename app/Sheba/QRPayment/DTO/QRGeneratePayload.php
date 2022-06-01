<?php

namespace App\Sheba\QRPayment\DTO;

use Sheba\Helpers\BasicGetter;

class QRGeneratePayload
{
    use BasicGetter;

    private $type;
    private $type_id;
    private $amount;
    private $payer_id;
    private $payer_type;
    private $payment_method;

    public function __construct(array $payload)
    {
        $this->type = $payload['type'] ?? null;
        $this->type_id = $payload['type_id'] ?? null;
        $this->amount = $payload['amount'] ?? null;
        $this->payer_id = $payload['payer_id'] ?? null;
        $this->payer_type = $payload['payer_type'] ?? null;
        $this->payment_method = $payload['payment_method'] ?? null;
    }
}