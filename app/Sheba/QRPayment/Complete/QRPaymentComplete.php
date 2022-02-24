<?php

namespace App\Sheba\QRPayment\Complete;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Carbon\Carbon;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;

abstract class QRPaymentComplete
{
    /*** @var QRPaymentModel */
    protected $qr_payment;

    protected $payable;

    protected $method;

    /**
     * @param mixed $qr_payment
     * @return QRPaymentComplete
     */
    public function setQrPayment(QRPaymentModel $qr_payment): QRPaymentComplete
    {
        $this->qr_payment = $qr_payment;
        return $this;
    }

    /**
     * @param mixed $payable
     * @return QRPaymentComplete
     */
    public function setPayable($payable): QRPaymentComplete
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @param mixed $method
     * @return QRPaymentComplete
     */
    public function setMethod($method): QRPaymentComplete
    {
        $this->method = $method;
        return $this;
    }

    public abstract function complete();
//
//    protected function storeAccountingEntry($source_id, $source_type)
//    {
//        $payload = $this->makeData($source_id, $source_type);
//        /** @var AccountingRepository $accounting_repo */
//        $accounting_repo = app()->make(AccountingRepository::class);
//        return $accounting_repo->storeEntry((object)$payload, EntryTypes::PAYMENT_LINK);
//    }
//
//    private function makeData($source_id, $source_type): array
//    {
//        $data['customer_id'] = $this->payable->user_id;
//        $data['amount'] = $this->payable->amount;
//        $data['amount_cleared'] = $this->payable->amount;
//        $data['entry_at'] = Carbon::now()->format('Y-m-d H:i:s');
//        $data['interest'] = 0;
//        $data['source_id'] = $source_id;
//        $data['source_type'] = $source_type;
//        $data['to_account_key'] = (new Accounts())->asset->cash::SSL;
//        $data['from_account_key'] = (new Accounts())->income->incomeFromPaymentLink::INCOME_FROM_PAYMENT_LINK;
//        $data['payment_type'] = "qr";
//        $data["payment_id"] = $this->qr_payment->id;
//        $data['partner'] = $this->payable->payee_id;
//        return $data;
//    }

}