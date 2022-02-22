<?php

namespace App\Sheba\QRPayment\Complete;

use App\Sheba\AccountingEntry\Constants\EntryTypes;

class AccountingDueComplete extends QRPaymentComplete
{
    public function complete()
    {
        $this->qr_payment->reload();
        if ($this->qr_payment->isComplete())
            return $this->qr_payment;

        $this->storeAccountingEntry($this->payable->target_id, EntryTypes::PAYMENT_LINK);
        return $this->qr_payment;
    }
}