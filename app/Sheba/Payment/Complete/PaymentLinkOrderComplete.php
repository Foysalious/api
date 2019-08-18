<?php namespace Sheba\Payment\Complete;

use App\Jobs\Partner\PaymentLink\SendPaymentLinkSms;
use App\Models\Payment;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Sheba\HasWallet;
use Sheba\ModificationFields;
use Sheba\PaymentLink\InvoiceCreator;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;
use DB;

class PaymentLinkOrderComplete extends PaymentComplete
{
    use ModificationFields;
    /** @var PaymentLinkRepository */
    private $paymentLinkRepository;
    /** @var PaymentLinkTransformer $paymentLink */
    private $paymentLink;
    private $paymentLinkCommission;
    /** @var InvoiceCreator $invoiceCreator */
    private $invoiceCreator;

    public function __construct()
    {
        parent::__construct();
        $this->paymentLinkRepository = app(PaymentLinkRepositoryInterface::class);
        $this->invoiceCreator = app(InvoiceCreator::class);
        $this->paymentLinkCommission = 2.5;
    }

    public function complete()
    {
        try {
            if ($this->payment->isComplete()) return $this->payment;
            $this->paymentLink = $this->getPaymentLink();
            $payment_receiver = $this->paymentLink->getPaymentReceiver();
            DB::transaction(function () use ($payment_receiver) {
                $this->paymentRepository->setPayment($this->payment);
                $payable = $this->payment->payable;
                $this->setModifier($customer = $payable->user);
                $this->payment->transaction_details = null;
                $this->completePayment();
                $this->processTransactions($payment_receiver);
                $this->clearPosOrder();
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        $this->payment = $this->saveInvoice();
        if ($this->paymentLink->getTarget()) {
            dispatch(new SendPaymentLinkSms($this->payment, $this->paymentLink));
            $this->notifyManager($this->payment, $this->paymentLink);
        }
        return $this->payment;
    }

    /**
     * @return PaymentLinkTransformer
     */
    private function getPaymentLink()
    {
        try {
            return $this->paymentLinkRepository->getPaymentLinkByLinkId($this->payment->payable->type_id);
        } catch (RequestException $e) {
            throw $e;
        }
    }

    private function clearPosOrder()
    {
        $target = $this->paymentLink->getTarget();
        if ($target) {
            $payment_data = [
                'pos_order_id' => $target->id,
                'amount' => $this->payment->payable->amount,
                'method' => $this->payment->payable->type
            ];
            $payment_creator = app(PaymentCreator::class);
            $payment_creator->credit($payment_data);
            $this->paymentLinkRepository->statusUpdate($this->paymentLink->getLinkID(), 0);
        }
    }


    private function processTransactions(HasWallet $payment_receiver)
    {
        $recharge_wallet_amount = $this->payment->payable->amount;
        $formatted_recharge_amount = number_format($recharge_wallet_amount, 2);
        $recharge_log = "$formatted_recharge_amount TK has been collected from {$this->payment->payable->getName()}, {$this->paymentLink->getReason()}";
        $recharge_transaction = $payment_receiver->rechargeWallet($recharge_wallet_amount, ['transaction_details' => $this->payment->getShebaTransaction()->toJson(), 'log' => $recharge_log]);
        $minus_wallet_amount = $this->getPaymentLinkFee($recharge_wallet_amount);
        $formatted_minus_amount = number_format($minus_wallet_amount, 2);
        $minus_log = "$formatted_minus_amount TK has been charged as link service fees against of Transc ID: {$recharge_transaction->id}, and Transc amount: $formatted_recharge_amount";
        $payment_receiver->minusWallet($minus_wallet_amount, ['log' => $minus_log]);
    }

    private function getPaymentLinkFee($amount)
    {
        return ($amount * $this->paymentLinkCommission) / 100;
    }

    protected function saveInvoice()
    {
        try {
            $this->payment->invoice_link = $this->invoiceCreator->setPaymentLink($this->paymentLink)->setPayment($this->payment)->save();
            $this->payment->update();
            return $this->payment;
        } catch (QueryException $e) {
            return null;
        }
    }

    private function notifyManager(Payment $payment, PaymentLinkTransformer $payment_link)
    {
        $partner = $payment_link->getPaymentReceiver();
        $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');

        (new PushNotificationHandler())->send([
            "title" => 'Order Successful',
            "message" => "{$payment_link->getAmount()}Tk has been collected from {$payment_link->getPayer()->profile->name} by order link- {$payment_link->getLinkID()}",
            "event_type" => 'PosOrder',
            "event_id" => $payment_link->getTarget()->id,
            "sound" => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);
    }

}
