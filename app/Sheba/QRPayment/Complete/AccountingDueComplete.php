<?php

namespace App\Sheba\QRPayment\Complete;

use App\Models\Payment;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Carbon\Carbon;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\QRPayment\Model as QRPayment;
use Sheba\Payment\Complete\PaymentComplete;

class AccountingDueComplete extends PaymentComplete
{
    private $payable;

    /**
     * @return QRPayment
     */
    public function complete()
    {
//        if ($this->isComplete()) return $this->getPayment();
//        $this->setPayable($this->getPayable());
//        $this->storeAccountingEntry($this->payable->target_id, EntryTypes::PAYMENT_LINK);
        return $this->qrPayment;
    }

    /**
     * @param mixed $payable
     */
    public function setPayable($payable)
    {
        $this->payable = $payable;
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}