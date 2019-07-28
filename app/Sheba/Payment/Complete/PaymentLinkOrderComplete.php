<?php namespace Sheba\Payment\Complete;

use App\Models\PosOrder;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Sheba\ModificationFields;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Repositories\PaymentLinkRepository;
use DB;

class PaymentLinkOrderComplete extends PaymentComplete
{
    use ModificationFields;
    /** @var PaymentLinkRepository */
    private $paymentLinkRepository;
    /** @var array $paymentLink */
    private $paymentLink;

    public function __construct()
    {
        parent::__construct();
        $this->paymentLinkRepository = new PaymentLinkRepository();
    }

    public function complete()
    {
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentLink = $this->getPaymentLink();
            DB::transaction(function () {
                $this->paymentRepository->setPayment($this->payment);
                $payable = $this->payment->payable;
                $this->setModifier($customer = $payable->user);
                $this->payment->transaction_details = null;
                $this->completePayment();
                $amount = $this->getAmountAfterCommission();
                $this->getPaymentLinkReceiver()->rechargeWallet($amount, [
                    'amount' => $amount,
                    'transaction_details' => $this->payment->getShebaTransaction()->toJson(),
                    'type' => 'Credit', 'log' => 'Credited through payment link payment'
                ]);
                $this->clearPosOrder();
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        return $this->payment;
    }

    private function getPaymentLink()
    {
        try {
            $response = $this->paymentLinkRepository->getPaymentLinkByLinkId($this->payment->payable->type_id);
            return $response['links'][0];
        } catch (RequestException $e) {
            throw $e;
        }
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
        if (strtolower($this->paymentLink['userType']) == 'partner') return ($this->payment->payable->amount * 97.5) / 100;
        else return $this->payment->payable->amount;
    }

    private function getPaymentLinkReceiver()
    {
        $model_name = "App\\Models\\" . ucfirst($this->paymentLink['userType']);
        return $model_name::find($this->paymentLink['userId']);
    }
}
