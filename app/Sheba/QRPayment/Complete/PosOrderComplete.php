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
     * @throws PosClientException
     */
    public function complete()
    {
        if ($this->isComplete()) return $this->getPayment();
        $payable = $this->getPayable();
        $this->clearOrder($payable);
//        $this->storeAccountingEntry($payable->target_id, EntryTypes::POS);
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
}