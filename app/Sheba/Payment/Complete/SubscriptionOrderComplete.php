<?php namespace Sheba\Payment\Complete;

use App\Models\Payment;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Dal\Discount\DiscountRepository;
use Sheba\ModificationFields;
use Throwable;
use DB;

class SubscriptionOrderComplete extends PaymentComplete
{
    use ModificationFields;

    /**
     * @return Payment
     * @throws Throwable
     */
    public function complete()
    {
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $this->setModifier($payable->user);
            $model = $payable->getPayableModel();
            /** @var SubscriptionOrder $subscription_order */
            $subscription_order = $model::find($payable->type_id);
            DB::transaction(function () use ($subscription_order) {
                $this->clearSubscriptionPayment($subscription_order);
                $this->completePayment();
                $subscription_order->payment_method = strtolower($this->payment->paymentDetails->last()->readable_method);
                $subscription_order->update();
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        return $this->payment;
    }


    /**
     * @param SubscriptionOrder $payable_model
     * @throws Throwable
     */
    private function clearSubscriptionPayment(SubscriptionOrder $payable_model)
    {
        $payable_model->status = 'paid';
        $payable_model->sheba_collection = (double)$this->payment->payable->amount;
        $payable_model->paid_at = Carbon::now();
        $payable_model->update();
        $this->convertToOrder($payable_model);
    }

    /**
     * @param SubscriptionOrder $payable_model
     */
    private function convertToOrder(SubscriptionOrder $payable_model)
    {
        $subscription_order = new SubscriptionOrderAdapter($payable_model);
        $subscription_order->convertToOrder();
    }
}