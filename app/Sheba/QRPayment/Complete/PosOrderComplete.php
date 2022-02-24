<?php

namespace App\Sheba\QRPayment\Complete;

use App\Models\Partner;
use App\Models\Payable;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use App\Sheba\Pos\Repositories\PosClientRepository;
use Carbon\Carbon;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\Dal\QRGateway\Model as QRGateway;
use Sheba\Payment\Complete\PaymentComplete;

class PosOrderComplete extends PaymentComplete
{
    public function complete()
    {
        if ($this->isComplete()) return $this->getPayment();
        $payable = $this->getPayable();
        $this->clearOrder($payable);
        $this->storeAccountingEntry($payable->target_id, EntryTypes::POS);
        return $this->qrPayment;
    }

    private function clearOrder(Payable $payable)
    {
        $payment_method_detail = QRGateway::where('method_name', $this->qrPayment->gateway_account_id)->first();

        $payment_data = [
            'amount' => $this->qrPayment->payable->amount,
            'payment_method' => "qrPayment",
            'payment_method_en' => $payment_method_detail->name,
            'payment_method_bn' => $payment_method_detail->name_bn,
            'payment_method_icon' => $payment_method_detail->icon,
            'interest' => 0,
        ];
        /** @var Partner $payee */
        $payee = $payable->payee;
        /** @var PosClientRepository $posOrderRepo */
        $posOrderRepo = app(PosClientRepository::class);
        $posOrderRepo->setPartnerId($payee->id)->setOrderId($payable->type_id)->addOnlinePayment($payment_data);
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }


    protected function storeAccountingEntry($source_id, $source_type)
    {
        $payload = $this->makeData($source_id, $source_type);
        /** @var AccountingRepository $accounting_repo */
        $accounting_repo = app()->make(AccountingRepository::class);
        return $accounting_repo->storeEntry((object)$payload, EntryTypes::PAYMENT_LINK);
    }

    private function makeData($source_id, $source_type): array
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