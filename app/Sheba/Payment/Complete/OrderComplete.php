<?php namespace Sheba\Payment\Complete;

use App\Models\PartnerOrder;
use App\Models\PaymentDetail;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Dal\Discount\DiscountRepository;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class OrderComplete extends PaymentComplete
{
    use ModificationFields;

    CONST ONLINE_PAYMENT_THRESHOLD_MINUTES = 9;
    CONST ONLINE_PAYMENT_DISCOUNT = 10;

    private $discountRepo;

    public function __construct(DiscountRepository $discount_repo)
    {
        parent::__construct();
        $this->discountRepo = $discount_repo;
    }

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
            if ($payable_model instanceof PartnerOrder) $this->giveOnlineDiscount($payable_model);
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


    private function clearPartnerOrderPayment(PartnerOrder $partner_order, $customer, PaymentDetail $paymentDetail, $has_error)
    {
        $client = new Client();
        /* @var PaymentDetail $paymentDetail */
        $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $partner_order->id . '/collect',
            [
                'form_params' => array_merge([
                    'customer_id' => $partner_order->order->customer->id,
                    'remember_token' => $partner_order->order->customer->remember_token,
                    'sheba_collection' => (double)$paymentDetail->amount,
                    'payment_method' => ucfirst($paymentDetail->method),
                    'created_by_type' => 'App\\Models\\Customer',
                    'transaction_detail' => json_encode($paymentDetail->formatPaymentDetail())
                ], (new RequestIdentification())->get())
            ]);
        $response = json_decode($res->getBody());
        if ($response->code == 200) {
            if (strtolower($paymentDetail->method) == 'wallet') dispatchReward()->run('wallet_cashback', $customer, $paymentDetail->amount, $partner_order);
        } else {
            $has_error = true;
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
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    private function giveOnlineDiscount(PartnerOrder $partner_order)
    {
        $partner_order->calculate(true);
        $job = $partner_order->getActiveJob();
        if ($job->isOnlinePaymentDiscountApplicable()) {
            $discount = $this->discountRepo->findValidFor(DiscountTypes::ONLINE_PAYMENT);
            if($discount) {
                $applied_amount = $discount->getApplicableAmount($partner_order->due);
                $job->discounts()->create($this->withBothModificationFields([
                    'discount_id' => $discount->id,
                    'type' => $discount->type,
                    'amount' => $applied_amount,
                    'original_amount' => $discount->amount,
                    'is_percentage' => $discount->is_percentage,
                    'cap' => $discount->cap,
                    'sheba_contribution' => $discount->sheba_contribution,
                    'partner_contribution' => $discount->partner_contribution,
                ]));

                $job->discount += $applied_amount;
                $job->update();
            }
        }
    }
}
