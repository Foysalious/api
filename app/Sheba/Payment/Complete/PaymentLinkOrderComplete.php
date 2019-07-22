<?php namespace Sheba\Payment\Complete;


use App\Models\PosOrder;
use App\Repositories\PaymentRepository;
use Sheba\ModificationFields;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Repositories\PaymentLinkRepository;

class PaymentLinkOrderComplete extends PaymentComplete
{

    use ModificationFields;

    public function complete()
    {
        $has_error = false;
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $this->setModifier($customer = $payable->user);
            $this->payment->transaction_details = null;
            $this->posOrderPaymentCheck();
            $this->completePayment();
        } catch (RequestException $e) {
            $this->failPayment();
            throw $e;
        }
        if ($has_error) {
            $this->completePayment();
        }
        return $this->payment;
    }

    private function posOrderPaymentCheck()
    {
        $repository = new PaymentLinkRepository();
        $response = $repository->getPaymentLinkByLinkId($this->payment->payable->type_id);
        try {
            if ($response['code'] == 200) {
                $linkDetails = $response['links'][0];
                if (isset($linkDetails['targetType']) && $linkDetails['targetType'] == 'pos_order') {
                    $order = PosOrder::find($linkDetails['targetId']);
                    $payment_data = [
                        'pos_order_id' => $order->id,
                        'amount' => $this->payment->payable->amount,
                        'method' => $this->payment->payable->type
                    ];
                    $payment_creator = new PaymentCreator(new PaymentRepository());
                    $payment_creator->credit($payment_data);
                    $repository->statusUpdate($linkDetails['linkId'], 0);
                    return true;
                }
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }
}
