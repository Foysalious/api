<?php namespace Sheba\Payment\Complete;

use App\Models\PartnerOrder;
use Sheba\Dal\PartnerOrderPayment\PartnerOrderPayment;
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
                $model = $payable->getPayableModel();
                $payable_model = $model::find((int)$payable->type_id);
                if ($payable_model instanceof PartnerOrder) {
                    $this->giveOnlineDiscount($payable_model);
                    $payable_model->sheba_collection = $this->payment->paymentDetails->sum('amount');
                    $payable_model->update();
                }
                $user = $payable->user;
                $this->setModifier($user);
                foreach ($this->payment->paymentDetails as $paymentDetail) {
                    /** @var PaymentDetail $paymentDetail */
                    if ($payable_model instanceof PartnerOrder) {
                        $partner_order_payment = new PartnerOrderPayment();
                        $partner_order_payment->partner_order_id = $payable_model->id;
                        $partner_order_payment->transaction_type = 'Debit';
                        $partner_order_payment->amount = $paymentDetail->amount;
                        $partner_order_payment->log = 'advanced payment';
                        $partner_order_payment->collected_by = 'Sheba';
                        $partner_order_payment->transaction_detail = json_encode($paymentDetail->formatPaymentDetail());
                        $partner_order_payment->method = ucfirst($paymentDetail->method);
                        $this->withCreateModificationField($partner_order_payment);
                        $partner_order_payment->fill((new RequestIdentification())->get());
                        $partner_order_payment->save();
                        if (strtolower($paymentDetail->method) == 'wallet') dispatchReward()->run('wallet_cashback', $user, $paymentDetail->amount, $payable_model);
                    }

                }
                $this->payment->transaction_details = null;
                $this->completePayment();
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        return $this->payment;
    }

    private function giveOnlineDiscount(PartnerOrder $partner_order)
    {
        $partner_order->calculate(true);
        $job = $partner_order->getActiveJob();
        if ($job->isOnlinePaymentDiscountApplicable()) {
            $job->online_discount = $partner_order->due * (config('sheba.online_payment_discount_percentage') / 100);
            $job->discount += $job->online_discount;
            $job->update();
        }
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}
