<?php

namespace App\Sheba\QRPayment\Complete;

use App\Models\Partner;
use App\Models\Payable;
use App\Models\Payment;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use App\Sheba\Pos\Exceptions\PosClientException;
use App\Sheba\Pos\Repositories\PosClientRepository;
use Carbon\Carbon;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\QRPayment\Model as QRPayment;
use Sheba\Payment\Complete\PaymentComplete;

class PosOrderComplete extends PaymentComplete
{
    /**
     * @return Payment|QRPayment
     * @throws AccountingEntryServerError
     * @throws PosClientException
     */
    public function complete()
    {
        if ($this->isComplete()) return $this->getPayment();
        $payable = $this->getPayable();
        $this->clearOrder($payable);
        $this->storeAccountingEntry($payable->target_id, EntryTypes::POS);
        return $this->qrPayment;
    }

    /**
     * @param Payable $payable
     * @return void
     * @throws PosClientException
     */
    private function clearOrder(Payable $payable)
    {
        $payment_data = $this->makePosEntryData();
        /** @var Partner $payee */
        $payee = $payable->payee;
        /** @var PosClientRepository $posOrderRepo */
        $posOrderRepo = app(PosClientRepository::class);
        $posOrderRepo->setPartnerId($payee->id)->setOrderId($payable->type_id)->addOnlinePayment($payment_data);
    }

    private function makePosEntryData(): array
    {
        $payment_method_detail = $this->qrPayment->qrGateway;

        return [
            'amount' => $this->qrPayment->payable->amount,
            'payment_method' => "qr_payment",
            'payment_method_en' => $payment_method_detail->name,
            'payment_method_bn' => $payment_method_detail->name_bn,
            'payment_method_icon' => $payment_method_detail->icon,
            'interest' => 0,
        ];
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }

    /**
     * @param $source_id
     * @param $source_type
     * @return bool|mixed
     * @throws AccountingEntryServerError
     */
    protected function storeAccountingEntry($source_id, $source_type)
    {
        $payload = $this->makeAccountingEntryData($source_id, $source_type);
        /** @var AccountingRepository $accounting_repo */
        $accounting_repo = app()->make(AccountingRepository::class);
        return $accounting_repo->storeEntry((object)$payload, EntryTypes::PAYMENT_LINK);
    }

    private function makeAccountingEntryData($source_id, $source_type): array
    {
        $data['customer_id'] = $this->qrPayment->payable->user_id;
        $data['amount'] = $this->qrPayment->payable->amount;
        $data['amount_cleared'] = $this->qrPayment->payable->amount;
        $data['entry_at'] = Carbon::now()->format('Y-m-d H:i:s');
        $data['interest'] = 0;
        $data['source_id'] = $source_id;
        $data['source_type'] = $source_type;
        $data['to_account_key'] = (new Accounts())->asset->cash::SSL;
        $data['from_account_key'] = (new Accounts())->income->incomeFromPaymentLink::INCOME_FROM_PAYMENT_LINK;
        $data['payment_type'] = "qr";
        $data["payment_id"] = $this->qrPayment->id;
        $data['partner'] = $this->qrPayment->payable->payee_id;
        return $data;
    }
}