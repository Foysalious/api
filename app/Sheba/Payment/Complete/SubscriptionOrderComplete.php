<?php namespace Sheba\Payment\Complete;

use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Dal\SubscriptionOrder\Statuses;
use Sheba\Dal\SubscriptionOrderPayment\SubscriptionOrderPaymentRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use Sheba\SubscriptionOrderRequest\Generator;
use Throwable;
use DB;

class SubscriptionOrderComplete extends PaymentComplete
{
    use ModificationFields;

    private $subscriptionOrderPaymentRepository;
    private $requestGenerator;

    public function __construct(SubscriptionOrderPaymentRepositoryInterface $subscription_order_payment_repository, Generator $generator)
    {
        parent::__construct();
        $this->subscriptionOrderPaymentRepository = $subscription_order_payment_repository;
        $this->requestGenerator = $generator;
    }

    /**
     * @return Payment
     * @throws Throwable
     * @throws Exception
     */
    public function complete()
    {
        try {
            $this->payment->reload();
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $this->setModifier($payable->user);
            $model = $payable->getPayableModel();
            /** @var SubscriptionOrder $subscription_order */
            $subscription_order = $model::find($payable->type_id);
            $payment_detail = $this->payment->paymentDetails->last();
            DB::transaction(function () use ($subscription_order, $payment_detail) {
                $this->clearSubscriptionPayment($subscription_order);
                if ($subscription_order->hasPartner()) $this->convertToOrder($subscription_order);
                $this->createSubscriptionOrderPaymentLog();
                $this->completePayment();
                $subscription_order->payment_method = strtolower($payment_detail->readable_method);
                $subscription_order->update();
            });
            $subscription_order = $subscription_order->fresh();
            $subscription_order->calculate();
            $this->cleaOrderPayment($subscription_order, $payment_detail);
            DB::transaction(function () use ($subscription_order) {
                if ($subscription_order->canCreateOrderRequest()) {
                    $this->requestGenerator->setSubscriptionOrder($subscription_order)->generate();
                    $subscription_order = $subscription_order->fresh();
                    if (!$subscription_order->hasOrderRequests()) $this->setSubscriptionOrderStatusToNotResponded($subscription_order);
                }
            });

        } catch (Exception $e) {
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

    /**
     * @param SubscriptionOrder $subscription_order
     * @param PaymentDetail $payment_detail
     * @throws Exception
     */
    private function cleaOrderPayment(SubscriptionOrder $subscription_order, PaymentDetail $payment_detail)
    {
        $client = new Client();
        foreach ($subscription_order->orders as $order) {
            foreach ($order->partnerOrders as $partner_order) {
                if ($partner_order->due <= 0) continue;
                $collection_url = config('sheba.admin_url') . '/api/partner-order/' . $partner_order->id . '/collect';
                $data = array_merge([
                    'customer_id' => $subscription_order->customer->id,
                    'remember_token' => $subscription_order->customer->remember_token,
                    'sheba_collection' => (double)$partner_order->due,
                    'payment_method' => ucfirst($payment_detail->method),
                    'created_by_type' => 'App\\Models\\Customer',
                    'transaction_detail' => json_encode($payment_detail->formatPaymentDetail())
                ], (new RequestIdentification())->get());

                try {
                    $res = $client->request('POST', $collection_url, ['form_params' => $data]);
                } catch (GuzzleException $e) {
                    throw new Exception('OrderComplete collect api call failure: ' . $e->getMessage());
                }

                $response = json_decode($res->getBody());

                if ($response->code != 200) {
                    throw new Exception('OrderComplete collect api failure. Response: ' . json_encode($response));
                }

                if (strtolower($payment_detail->method) == 'wallet') {
                    dispatchReward()->run('wallet_cashback', $subscription_order->customer, $payment_detail->amount, $partner_order);
                }

            }
        }

    }

    private function setSubscriptionOrderStatusToNotResponded(SubscriptionOrder $subscriptionOrder)
    {
        $subscriptionOrder->status = Statuses::NOT_RESPONDED;
        $subscriptionOrder->update();
    }

}
