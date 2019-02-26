<?php namespace Sheba\Payment\Complete;

use App\Models\PartnerOrder;
use App\Models\PaymentDetail;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\Checkout\SubscriptionOrder;
use Sheba\RequestIdentification;

class OrderComplete extends PaymentComplete
{
    public function complete()
    {
        $has_error = false;
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $model = $payable->getPayableModel();
            $payable_model = $model::find((int)$payable->type_id);
            $customer = $payable->user;
            foreach ($this->payment->paymentDetails as $paymentDetail) {
                dd($payable_model);
                if ($payable_model instanceof PartnerOrder) {
                    $has_error = $this->clearPartnerOrderPayment($payable_model, $customer, $paymentDetail, $has_error);
                } else {
                    $has_error = $this->clearSubscriptionPayment($payable_model, $paymentDetail, $has_error);
                }
            }
            $this->paymentRepository->changeStatus(['to' => 'completed', 'from' => $this->payment->status,
                'transaction_details' => $this->payment->transaction_details]);
            $this->payment->status = 'completed';
            $this->payment->transaction_details = null;
            $this->payment->update();

            $payable_model->payment_method = strtolower($paymentDetail->readable_method);
            $payable_model->update();
        } catch (RequestException $e) {
            $this->paymentRepository->changeStatus(['to' => 'failed', 'from' => $this->payment->status,
                'transaction_details' => $this->payment->transaction_details]);
            $this->payment->status = 'failed';
            $this->payment->update();
            throw $e;
        }
        if ($has_error) {
            $this->paymentRepository->changeStatus(['to' => 'completed', 'from' => $this->payment->status,
                'transaction_details' => $this->payment->transaction_details]);
            $this->payment->status = 'completed';
            $this->payment->update();
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
                    'customer_id' => $customer->id,
                    'remember_token' => $customer->remember_token,
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
            $payable_model->update();
            dd($payable_model);
        } catch (\Throwable $e) {

            $has_error = false;
        }
        return $has_error;
    }
}