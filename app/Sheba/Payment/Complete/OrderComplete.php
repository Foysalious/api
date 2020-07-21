<?php namespace Sheba\Payment\Complete;

use Illuminate\Support\Facades\Redis;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use App\Models\SubscriptionOrder;
use Sheba\RequestIdentification;
use App\Models\PaymentDetail;
use App\Models\PartnerOrder;
use App\Models\Payment;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Exception;
use Sheba\Resource\Jobs\SendOnlinePaymentNotificationToResource;
use Throwable;

class OrderComplete extends BaseOrderComplete
{

    CONST ONLINE_PAYMENT_THRESHOLD_MINUTES = 9;
    CONST ONLINE_PAYMENT_DISCOUNT = 10;

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
                $this->giveOnlineDiscount($payable_model);
            }

            foreach ($this->payment->paymentDetails as $payment_detail) {
                if ($payment_detail->amount == 0) continue;
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
            if ($payable_model instanceof PartnerOrder) {
                $payable_model->fresh();
                $payable_model->calculate();
                if ($payable_model->due == 0) dispatch((new SendOnlinePaymentNotificationToResource($payable_model->lastJob()->resource_id, $payable_model->lastJob())));
            }
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
            throw new Exception('OrderComplete collect api failure. Response: ' . json_encode($response));
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

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}
