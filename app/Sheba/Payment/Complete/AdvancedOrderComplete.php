<?php

namespace Sheba\Payment\Complete;


use App\Models\Customer;
use App\Models\PartnerOrder;
use App\Models\PartnerOrderPayment;
use App\Models\Payment;
use Illuminate\Database\QueryException;
use Sheba\ModificationFields;
use Sheba\Payment\PayChargable;
use DB;
use Sheba\RequestIdentification;
use Sheba\Reward\ActionRewardDispatcher;

class AdvancedOrderComplete extends PaymentComplete
{
    use ModificationFields;

    public function complete()
    {
        try {
            DB::transaction(function () {
                $payable = $this->payment->payable;
                $partner_order = PartnerOrder::find((int)$payable->type_id);
                $partner_order->sheba_collection = $payable->amount;
                $partner_order->update();
                $user = $payable->user;
                $this->setModifier($user);
                foreach ($this->payment->paymentDetails as $paymentDetail) {
                    $partner_order_payment = new PartnerOrderPayment();
                    $partner_order_payment->partner_order_id = $partner_order->id;
                    $partner_order_payment->transaction_type = 'Debit';
                    $partner_order_payment->amount = $paymentDetail->amount;
                    $partner_order_payment->log = 'advanced payment';
                    $partner_order_payment->collected_by = 'Sheba';
                    $partner_order_payment->transaction_detail = json_encode($paymentDetail->formatPaymentDetail());
                    $partner_order_payment->method = $paymentDetail->method;
                    $this->withCreateModificationField($partner_order_payment);
                    $partner_order_payment->fill((new RequestIdentification())->get());
                    $partner_order_payment->save();
                    if (strtolower($paymentDetail->name) == 'wallet') {
                        app(ActionRewardDispatcher::class)->run(
                            'wallet_cashback',
                            $user,
                            $paymentDetail->amount,
                            $partner_order
                        );
                    }
                }
                $this->payment->status = 'completed';
                $this->payment->update();
            });
        } catch (QueryException $e) {
            $this->payment->status = 'failed';
            $this->payment->update();
            throw $e;
        }
        return $this->payment;
    }
}