<?php namespace Sheba\Payment\Complete;

use App\Models\PosOrder;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Sheba\HasWallet;
use Sheba\ModificationFields;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;
use DB;

class PaymentLinkOrderComplete extends PaymentComplete
{
    use ModificationFields;
    /** @var PaymentLinkRepository */
    private $paymentLinkRepository;
    /** @var array $paymentLink */
    private $paymentLink;
    private $paymentLinkCommission;

    public function __construct()
    {
        parent::__construct();
        $this->paymentLinkRepository = app(PaymentLinkRepositoryInterface::class);
        $this->paymentLinkCommission = 2.5;
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
                $recharge_wallet_amount = $this->payment->payable->amount;
                $payment_receiver = $this->getPaymentLinkReceiver();
                $payment_receiver->rechargeWallet($recharge_wallet_amount, [
                    'transaction_details' => $this->payment->getShebaTransaction()->toJson(),
                    'log' => 'Credited through payment link payment'
                ]);
                $payment_receiver->minusWallet($this->getPaymentLinkFee($recharge_wallet_amount), ['log' => 'Amount deducted for as PaymentLink charge']);
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

    /**
     * @return HasWallet
     */
    private function getPaymentLinkReceiver()
    {
        $model_name = "App\\Models\\" . ucfirst($this->paymentLink['userType']);
        return $model_name::find($this->paymentLink['userId']);
    }
    
    private function getPaymentLinkFee($amount)
    {
        return ($amount * $this->paymentLinkCommission) / 100;
    }

}
