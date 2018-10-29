<?php

namespace Sheba\Payment\Complete;


use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Models\PaymentDetail;
use Illuminate\Database\QueryException;
use Sheba\ModificationFields;
use DB;
use Sheba\RequestIdentification;

class AdvancedOrderComplete extends PaymentComplete
{
    use ModificationFields;

    public function complete()
    {
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            DB::transaction(function () {
                $payable = $this->payment->payable;
                $partner_order = PartnerOrder::find((int)$payable->type_id);
                $partner_order->sheba_collection = $payable->amount;
                $partner_order->update();
                $user = $payable->user;
                $this->setModifier($user);
                foreach ($this->payment->paymentDetails as $paymentDetail) {
                    /** @var PaymentDetail $paymentDetail */
                    $partner_order_payment = new PartnerOrderPayment();
                    $partner_order_payment->partner_order_id = $partner_order->id;
                    $partner_order_payment->transaction_type = 'Debit';
                    $partner_order_payment->amount = $paymentDetail->amount;
                    $partner_order_payment->log = 'advanced payment';
                    $partner_order_payment->collected_by = 'Sheba';
                    $partner_order_payment->transaction_detail = json_encode($paymentDetail->formatPaymentDetail());
                    $partner_order_payment->method = ucfirst($paymentDetail->method);
                    $this->withCreateModificationField($partner_order_payment);
                    $partner_order_payment->fill((new RequestIdentification())->get());
                    $partner_order_payment->save();
                    if (strtolower($paymentDetail->method) == 'wallet') {
                        dispatchReward()->run('wallet_cashback', $user, $paymentDetail->amount, $partner_order);
                    }
                }
                $this->paymentRepository->changeStatus(['to' => 'completed', 'from' => $this->payment->status,
                    'transaction_details' => $this->payment->transaction_details]);
                $this->payment->status = 'completed';
                $this->payment->transaction_details = null;
                $this->payment->update();
            });
        } catch (QueryException $e) {
            $this->paymentRepository->changeStatus(['to' => 'failed', 'from' => $this->payment->status,
                'transaction_details' => $this->payment->transaction_details]);
            $this->payment->status = 'failed';
            $this->payment->update();
            throw $e;
        }
        return $this->payment;
    }
}