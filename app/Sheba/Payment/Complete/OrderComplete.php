<?php namespace Sheba\Payment\Complete;

use App\Models\PartnerOrder;
use App\Models\PaymentDetail;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\RequestIdentification;

class OrderComplete extends PaymentComplete
{
    public function complete()
    {
        $has_error = false;
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $client = new Client();
            $payable = $this->payment->payable;
            $partner_order = PartnerOrder::find((int)$payable->type_id);
            $customer = $payable->user;
            foreach ($this->payment->paymentDetails as $paymentDetail) {
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
            }
            $this->paymentRepository->changeStatus(['to' => 'completed', 'from' => $this->payment->status,
                'transaction_details' => $this->payment->transaction_details]);
            $this->payment->status = 'completed';
            $this->payment->transaction_details = null;
            $this->payment->update();

            $partner_order->payment_method = strtolower($paymentDetail->readable_method);
            $partner_order->update();
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
}