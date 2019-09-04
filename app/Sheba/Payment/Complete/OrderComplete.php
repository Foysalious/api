<?php namespace Sheba\Payment\Complete;

use App\Models\PartnerOrder;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use Throwable;

class OrderComplete extends PaymentComplete
{
    use ModificationFields;

    CONST ONLINE_PAYMENT_THRESHOLD_MINUTES = 9;
    CONST ONLINE_PAYMENT_DISCOUNT = 10;

    private $jobDiscountHandler;

    public function __construct(JobDiscountHandler $job_discount_handler)
    {
        parent::__construct();
        $this->jobDiscountHandler = $job_discount_handler;
    }

    /**
     * @return Payment
     * @throws Exception
     * @throws GuzzleException
     */
    public function complete()
    {
        $has_error = false;
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $this->setModifier($customer = $payable->user);
            $model = $payable->getPayableModel();
            $payable_model = $model::find((int)$payable->type_id);
            if ($payable_model instanceof PartnerOrder) {
                $this->giveOnlineDiscount($payable_model, $this->payment->paymentDetails[0]->method);
            }
            foreach ($this->payment->paymentDetails as $payment_detail) {
                if ($payable_model instanceof PartnerOrder) {
                    $has_error = $this->clearPartnerOrderPayment($payable_model, $customer, $payment_detail, $has_error);
                } elseif ($payable_model instanceof SubscriptionOrder) {
                    $has_error = $this->clearSubscriptionPayment($payable_model, $payment_detail, $has_error);
                }
            }
            $this->payment->transaction_details = null;
            $this->completePayment();
            $payable_model->payment_method = strtolower($payment_detail->readable_method);
            $payable_model->update();
        } catch (RequestException $e) {
            $this->failPayment();
            throw $e;
        }
        if ($has_error) {
            $this->completePayment();
        }
        return $this->payment;
    }


    /**
     * @param PartnerOrder $partner_order
     * @param $customer
     * @param PaymentDetail $payment_detail
     * @param $has_error
     * @return bool
     * @throws GuzzleException
     * @throws Exception
     */
    private function clearPartnerOrderPayment(PartnerOrder $partner_order, $customer, PaymentDetail $payment_detail, $has_error)
    {
        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $partner_order->id . '/collect',
            [
                'form_params' => array_merge([
                    'customer_id' => $partner_order->order->customer->id,
                    'remember_token' => $partner_order->order->customer->remember_token,
                    'sheba_collection' => (double)$payment_detail->amount,
                    'payment_method' => ucfirst($payment_detail->method),
                    'created_by_type' => 'App\\Models\\Customer',
                    'transaction_detail' => json_encode($payment_detail->formatPaymentDetail())
                ], (new RequestIdentification())->get())
            ]);
        $response = json_decode($res->getBody());
        if ($response->code == 200) {
            if (strtolower($payment_detail->method) == 'wallet') dispatchReward()->run('wallet_cashback', $customer, $payment_detail->amount, $partner_order);
        } else {
            $has_error = true;
            throw new Exception('OrderComplete collect api failure. code:' . $response->code);
        }

        return $has_error;
    }

    private function clearSubscriptionPayment(SubscriptionOrder $payable_model, PaymentDetail $paymentDetail, $has_error)
    {
        try {
            $payable_model->status = 'paid';
            $payable_model->sheba_collection = (double)$paymentDetail->amount;
            $payable_model->paid_at = Carbon::now();
            $payable_model->update();
            $this->convertToOrder($payable_model);
        } catch (Throwable $e) {
            $has_error = false;
        }
        return $has_error;
    }

    /**
     * @param SubscriptionOrder $payable_model
     */
    private function convertToOrder(SubscriptionOrder $payable_model)
    {
        try {
            $subscription_order = new SubscriptionOrderAdapter($payable_model);
            $subscription_order->convertToOrder();
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    /**
     * @param PartnerOrder $partner_order
     * @param $payment_method
     * @throws InvalidDiscountType
     */
    public function giveOnlineDiscount(PartnerOrder $partner_order, $payment_method)
    {
        $partner_order->calculate(true);
        $job = $partner_order->getActiveJob();
        if ($job->isOnlinePaymentDiscountApplicable()) {

            $discount_checking_params = (new JobDiscountCheckingParams())
                ->setDiscountableAmount($partner_order->due)
                ->setOrderAmount($partner_order->grossAmount)
                ->setPaymentGateway($payment_method);

            $this->jobDiscountHandler->setType(DiscountTypes::ONLINE_PAYMENT)
                ->setCheckingParams($discount_checking_params)->calculate();

            if ($this->jobDiscountHandler->hasDiscount()) {
                $this->jobDiscountHandler->create($job);
                $job->discount += $this->jobDiscountHandler->getApplicableAmount();
                $job->update();
            }
        }
    }

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}
