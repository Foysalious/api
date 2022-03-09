<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use App\Sheba\QRPayment\Methods\QRPaymentMethod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\Statuses;
use Throwable;

class QRPaymentManager extends PaymentManager
{
    /*** @var Payable */
    private $payable;
    /*** @var QRPaymentModel */
    private $qrPayment;
    private $method;

    /**
     * @param mixed $qr_payment
     * @return QRPaymentManager
     */
    public function setQrPayment(QRPaymentModel $qr_payment): QRPaymentManager
    {
        $this->qrPayment = $qr_payment;
        return $this->setPayable($this->qrPayment->payable);
    }

    /**
     * @param mixed $payable
     * @return QRPaymentManager
     */
    public function setPayable(Payable $payable): QRPaymentManager
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @param $method_name
     * @return QRPaymentMethod|void
     * @throws InvalidPaymentMethod
     */
    public function getQRMethod($method_name)
    {
        if ($this->method) return $this->method;
        $this->method = PaymentStrategy::getQRMethod($method_name);
        return $this->method;
    }

    /**
     * @throws Throwable
     * @throws AlreadyCompletingPayment
     */
    public function complete()
    {
        $this->runningCompletionCheckAndSet();
        try {
            if (!$this->qrPayment->canComplete()) return $this->qrPayment;
            if(isset($this->payable->completion_type)) {
                $completion_class = $this->payable->getCompletionClass();
                $payment = $completion_class->setQrPayment($this->qrPayment)->setMethod($this->qrPayment->qrGateway->method_name)->complete();
            }
            $this->accountingEntry();
            $this->completePayment();
            $this->unsetRunningCompletion();
            return $payment ?? $this->qrPayment;
        } catch (Throwable $e) {
            $this->unsetRunningCompletion();
            throw $e;
        }
    }

    /**
     * @throws AlreadyCompletingPayment
     */
    private function runningCompletionCheckAndSet()
    {
        $key = $this->getKey();
        $already = Redis::get($key);
        if ($already) {
            throw new AlreadyCompletingPayment();
        }
        Redis::set($key, 1);
    }

    private function unsetRunningCompletion()
    {
        Redis::del($this->getKey());
    }

    private function getKey()
    {
        return 'QR_Payment::Completing::' . $this->qrPayment->id;
    }

    private function completePayment()
    {
        $this->qrPayment->reload();

        if ($this->qrPayment->status !== Statuses::COMPLETED) {
            $this->qrPayment->status = Statuses::COMPLETED;
            $this->qrPayment->save();
        }
    }

    /**
     * @return void
     * @throws AccountingEntryServerError
     */
    public function accountingEntry()
    {
        $this->storeAccountingEntry($this->payable->target_id, EntryTypes::PAYMENT_LINK);
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
}