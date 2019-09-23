<?php namespace Sheba\Payment\Complete;

use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Dal\SubscriptionOrderPayment\SubscriptionOrderPaymentRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use Throwable;
use DB;

class SubscriptionOrderComplete extends PaymentComplete
{
    use ModificationFields;
    private $subscriptionOrderPaymentRepository;

    public function __construct(SubscriptionOrderPaymentRepositoryInterface $subscription_order_payment_repository)
    {
        parent::__construct();
        $this->subscriptionOrderPaymentRepository = $subscription_order_payment_repository;
    }

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
                $this->createSubscriptionOrderPayment();
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

    private function createSubscriptionOrderPayment()
    {
        /** @var PaymentDetail $payment_detail */
        $payment_detail = $this->payment->paymentDetails->first();
        $this->subscriptionOrderPaymentRepository->create(
            array_merge([
                'subscription_order_id' => $this->payment->payable->type_id,
                'amount' => $this->payment->payable->amount,
                'transaction_type' => 'Debit',
                'method' => ucfirst($payment_detail->method),
                'created_by_type' => get_class($this->payment->payable->user),
                'transaction_detail' => json_encode($payment_detail->formatPaymentDetail()),
                'created_by' => $this->payment->payable->user->id,
                'log' => 'Subscription Order #' . $this->payment->payable->type_id . ' payment.'
            ], (new RequestIdentification())->get())
        );
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

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}