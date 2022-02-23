<?php

namespace App\Sheba\QRPayment\Complete;

use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\Pos\Repositories\PosClientRepository;
use Sheba\Dal\QRGateway\Model as QRGateway;

class PosOrderComplete extends QRPaymentComplete
{
    public function complete()
    {
        $this->qr_payment->reload();
        if ($this->qr_payment->isComplete())
            return $this->qr_payment;
        $this->clearOrder();
        $this->storeAccountingEntry($this->payable->target_id, EntryTypes::POS);
        return $this->qr_payment;
    }

    private function clearOrder()
    {
        $payment_data    = $this->makeData();
        $partner = $this->payable->payee;


        /** @var PosClientRepository $posOrderRepo */
        $posOrderRepo = app(PosClientRepository::class);
        $posOrderRepo->setPartnerId($partner->id)->setOrderId($this->payable->type_id)->addOnlinePayment($payment_data);
    }

    private function makeData(): array
    {
        $payment_method_detail = QRGateway::where('method_name', $this->method)->first();

        return [
            'amount'              => $this->payable->amount,
            'payment_method'      => "qr_payment",
            'payment_method_en'   => $payment_method_detail->name,
            'payment_method_bn'   => $payment_method_detail->name_bn,
            'payment_method_icon' => $payment_method_detail->icon,
            'interest'            => 0,
        ];
    }
}