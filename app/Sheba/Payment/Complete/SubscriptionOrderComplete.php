<?php namespace Sheba\Payment\Complete;

use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
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
            $payment_detail = $this->payment->paymentDetails->last();
            DB::transaction(function () use ($subscription_order, $payment_detail) {
                $this->createSubscriptionOrderPaymentLog();
                $this->clearSubscriptionPayment($subscription_order);
                $this->completePayment();
                $subscription_order->payment_method = strtolower($payment_detail->readable_method);
                $subscription_order->update();
                $subscription_order->calculate();
                $this->cleaOrderPayment($subscription_order, $payment_detail);
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        return $this->payment;
    }

    private function createSubscriptionOrderPaymentLog()
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


    private function convertToOrder(SubscriptionOrder $payable_model)
    {
        $subscription_order = new SubscriptionOrderAdapter($payable_model);
        return $subscription_order->convertToOrder();
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }

    private function cleaOrderPayment(SubscriptionOrder $subscription_order, PaymentDetail $payment_detail)
    {
        $client = new Client();
        foreach ($subscription_order->orders as $order) {
            foreach ($order->partnerOrders as $partner_order) {
                if ($partner_order->due <= 0) continue;
                $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $partner_order->id . '/collect',
                    [
                        'form_params' => array_merge([
                            'customer_id' => $subscription_order->customer->id,
                            'remember_token' => $subscription_order->customer->remember_token,
                            'sheba_collection' => (double)$partner_order->due,
                            'payment_method' => ucfirst($payment_detail->method),
                            'created_by_type' => 'App\\Models\\Customer',
                            'transaction_detail' => json_encode($payment_detail->formatPaymentDetail())
                        ], (new RequestIdentification())->get())
                    ]);
                $response = json_decode($res->getBody());
                if ($response->code == 200) {
                    if (strtolower($payment_detail->method) == 'wallet') dispatchReward()->run('wallet_cashback', $subscription_order->customer, $payment_detail->amount, $partner_order);
                } else {
                    throw new Exception('OrderComplete collect api failure. Response: ' . json_encode($response));
                }

            }
        }

    }
}