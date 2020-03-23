<?php namespace Sheba\Payment\Complete;

use App\Jobs\Partner\PaymentLink\SendPaymentLinkSms;
use App\Models\Payment;
use App\Models\PosOrder;
use DB;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\PaymentLink\InvoiceCreator;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class PaymentLinkOrderComplete extends PaymentComplete
{
    use DispatchesJobs;
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
        $this->invoiceCreator        = app(InvoiceCreator::class);
        $this->paymentLinkCommission = 2.5;
    }

    public function complete()
    {
        try {
            if ($this->payment->isComplete())
                return $this->payment;
            $this->paymentLink = $this->getPaymentLink();
            $payment_receiver  = $this->paymentLink->getPaymentReceiver();
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
        $target        = $this->paymentLink->getTarget();
        if ($target) {
            $payment      = $this->payment;
            $payment_link = $this->paymentLink;
            dispatch(new SendPaymentLinkSms($payment, $payment_link));
            $this->notifyManager($this->payment, $this->paymentLink);
        }
        $payable = $this->payment->payable;
        app(ActionRewardDispatcher::class)->run('payment_link_usage', $payment_receiver, $payment_receiver, $payable);
        /** @var AutomaticEntryRepository $entry_repo */
        $entry_repo = app(AutomaticEntryRepository::class)->setPartner($payment_receiver)->setAmount($payable->amount)->setHead(AutomaticIncomes::PAYMENT_LINK);
        if ($target instanceof PosOrder) {
            $entry_repo->setCreatedAt($target->created_at);
            $entry_repo->setSourceType(class_basename($target));
            $entry_repo->setSourceId($target->id);
        }
        if ($payer = $this->paymentLink->getPayer()) {
            $entry_repo->setParty($payer);
        }
        $entry_repo->store();
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

    /**
     * @param HasWalletTransaction $payment_receiver
     */
    private function processTransactions(HasWalletTransaction $payment_receiver)
    {
        $walletTransactionHandler  = (new WalletTransactionHandler())->setModel($payment_receiver);
        $recharge_wallet_amount    = $this->payment->payable->amount;
        $formatted_recharge_amount = number_format($recharge_wallet_amount, 2);
        $recharge_log              = "$formatted_recharge_amount TK has been collected from {$this->payment->payable->getName()}, {$this->paymentLink->getReason()}";
        $recharge_transaction      = $walletTransactionHandler->setType('credit')->setAmount($recharge_wallet_amount)->setSource(TransactionSources::PAYMENT_LINK)->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setLog($recharge_log)->store();
        $minus_wallet_amount       = $this->getPaymentLinkFee($recharge_wallet_amount);
        $formatted_minus_amount    = number_format($minus_wallet_amount, 2);
        $minus_log                 = "$formatted_minus_amount TK has been charged as link service fees against of Transc ID: {$recharge_transaction->id}, and Transc amount: $formatted_recharge_amount";
        $walletTransactionHandler->setLog($minus_log)->setType('debit')->setAmount($minus_wallet_amount)->setTransactionDetails([])->setSource(TransactionSources::PAYMENT_LINK)->store();
        /*$payment_receiver->minusWallet($minus_wallet_amount, ['log' => $minus_log]);*/

    }

    private function getPaymentLinkFee($amount)
    {
        return ($amount * $this->paymentLinkCommission) / 100;
    }

    private function clearPosOrder()
    {
        $target = $this->paymentLink->getTarget();
        if ($target) {
            $payment_data    = [
                'pos_order_id' => $target->id,
                'amount'       => $this->payment->payable->amount,
                'method'       => $this->payment->payable->type
            ];
            $payment_creator = app(PaymentCreator::class);
            $payment_creator->credit($payment_data);
            $this->paymentLinkRepository->statusUpdate($this->paymentLink->getLinkID(), 0);
        }
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

    /**
     * @param Payment $payment
     * @param PaymentLinkTransformer $payment_link
     */
    private function notifyManager(Payment $payment, PaymentLinkTransformer $payment_link)
    {
        $partner          = $payment_link->getPaymentReceiver();
        $topic            = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel          = config('sheba.push_notification_channel_name.manager');
        $sound            = config('sheba.push_notification_sound.manager');
        $formatted_amount = number_format($payment_link->getAmount(), 2);
        (new PushNotificationHandler())->send([
            "title"      => 'Order Successful',
            "message"    => "$formatted_amount Tk has been collected from {$payment_link->getPayer()->name} by order link- {$payment_link->getLinkID()}",
            "event_type" => 'PosOrder',
            "event_id"   => $payment_link->getTarget()->id,
            "sound"      => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);
    }
}
