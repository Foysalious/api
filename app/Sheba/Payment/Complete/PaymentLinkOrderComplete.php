<?php namespace Sheba\Payment\Complete;

use App\Models\Partner;
use App\Models\PosOrder;
use GuzzleHttp\Exception\RequestException;
use Sheba\ModificationFields;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Repositories\PaymentLinkRepository;

class PaymentLinkOrderComplete extends PaymentComplete
{
    use ModificationFields;
    private $paymentLinkRepository;
    private $paymentLink;

    public function __construct()
    {
        parent::__construct();
        $this->paymentLinkRepository = new PaymentLinkRepository();
        $this->paymentLink = $this->getPaymentLink();
    }

    public function complete()
    {
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentRepository->setPayment($this->payment);
            $payable = $this->payment->payable;
            $this->setModifier($customer = $payable->user);
            $this->payment->transaction_details = null;
            $this->clearPosOrder();
            $this->completePayment();
            $amount = $this->getAmountAfterCommission();
            $this->payment->payable->user->rechargeWallet($amount, [
                'amount' => $amount,
                'transaction_details' => $this->payment->getShebaTransaction()->toJson(),
                'type' => 'Credit', 'log' => 'Credited through payment link payment'
            ]);
        } catch (RequestException $e) {
            $this->failPayment();
            throw $e;
        }
        return $this->payment;
    }

    private function getPaymentLink()
    {
        $response = $this->paymentLinkRepository->getPaymentLinkByLinkId($this->payment->payable->type_id);
        if ($response['code'] == 200 && $response['links'][0]) $this->paymentLink = $response['links'][0];
        else return null;
    }

    private function clearPosOrder()
    {
        if (isset($this->paymentLink['targetType']) && $this->paymentLink['targetType'] == 'pos_order') {
            $order = PosOrder::find($this->paymentLink['targetId']);
            $payment_data = [
                'pos_order_id' => $order->id,
                'amount' => $this->payment->payable->amount,
                'method' => $this->payment->payable->type
            ];
            $payment_creator = app(PaymentCreator::class);
            $payment_creator->credit($payment_data);
            $this->paymentLinkRepository->statusUpdate($this->paymentLink['linkId'], 0);
        }
    }

    private function getAmountAfterCommission()
    {
        if ($this->payment->payable->user instanceof Partner) return ($this->payment->payable->amount * 97.5) / 100;
        else return $this->payment->payable->amount;
    }
}
