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
     * @return Payment|QRPayment
     * @throws AccountingEntryServerError
     */
    public function complete()
    {
        if ($this->isComplete()) return $this->getPayment();
        $this->setPayable($this->getPayable());
        $this->storeAccountingEntry($this->payable->target_id, EntryTypes::PAYMENT_LINK);
        return $this->qrPayment;
    }

    /**
     * @param $source_id
     * @param $source_type
     * @return bool|mixed
     * @throws AccountingEntryServerError
     */
    protected function storeAccountingEntry($source_id, $source_type)
    {
        $payload = $this->makeAccountingData($source_id, $source_type);
        /** @var AccountingRepository $accounting_repo */
        $accounting_repo = app()->make(AccountingRepository::class);
        return $accounting_repo->storeEntry((object)$payload, EntryTypes::PAYMENT_LINK);
    }

    /**
     * @param $source_id
     * @param $source_type
     * @return array
     */
    private function makeAccountingData($source_id, $source_type): array
    {
        $data['customer_id'] = $this->payable->user_id;
        $data['amount'] = $this->payable->amount;
        $data['amount_cleared'] = $this->payable->amount;
        $data['entry_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $data['interest'] = 0;
        $data['source_id'] = $source_id;
        $data['source_type'] = $source_type;
        $data['to_account_key'] = (new Accounts())->asset->cash::SSL;
        $data['from_account_key'] = (new Accounts())->income->incomeFromPaymentLink::INCOME_FROM_PAYMENT_LINK;
        $data['payment_type'] = "qr";
        $data["payment_id"] = $this->qrPayment->id;
        $data['partner'] = $this->payable->payee_id;
        return $data;
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