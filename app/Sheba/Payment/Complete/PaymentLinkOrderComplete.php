<?php namespace Sheba\Payment\Complete;

use App\Jobs\Partner\PaymentLink\SendPaymentLinkSms;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PosOrder;
use App\Models\Profile;
use App\Sheba\Pos\Order\PosOrderObject;
use DB;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\ExpenseTracker\AutomaticExpense;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\PaymentLink\InvoiceCreator;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Pos\Order\PosOrderResolver;
use Sheba\Pos\Order\PosOrderTypes;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Usage\Usage;
use Sheba\Dal\ExternalPayment\Model as ExternalPayment;

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
    private $target;
    private $payment_receiver;

    public function __construct()
    {
        parent::__construct();
        $this->paymentLinkRepository = app(PaymentLinkRepositoryInterface::class);
        $this->invoiceCreator        = app(InvoiceCreator::class);
        $this->paymentLinkCommission = 2;
    }

    public function complete()
    {
        try {
            if ($this->payment->isComplete())
                return $this->payment;
            $this->paymentLink      = $this->getPaymentLink();
            $this->payment_receiver = $this->paymentLink->getPaymentReceiver();
            DB::transaction(function () {
                $this->paymentRepository->setPayment($this->payment);
                $payable = $this->payment->payable;
                $this->setModifier($customer = $payable->user);
                $this->payment->transaction_details = null;
                $this->completePayment();
                $this->processTransactions($this->payment_receiver);
                $this->clearTarget();
                $this->createUsage($this->payment_receiver, $payable->user);
            });
        } catch (QueryException $e) {
            $this->failPayment();
            throw $e;
        }
        try {
            $this->payment = $this->saveInvoice();
            $this->notify();
            $this->dispatchReward();
            $this->storeEntry();
        }catch (\Throwable $e){
            logError($e);
        }
        return $this->payment;
    }

    private function storeEntry()
    {

        $payable = $this->payment->payable;
        /** @var AutomaticEntryRepository $entry_repo */
        $entry_repo = app(AutomaticEntryRepository::class)->setPartner($this->payment_receiver)->setAmount($payable->amount)->setHead(AutomaticIncomes::PAYMENT_LINK)
                                                          ->setEmiMonth($payable->emi_month)->setAmountCleared($payable->amount);
        $entry_repo->setInterest($this->paymentLink->getInterest())->setBankTransactionCharge($this->paymentLink->getBankTransactionCharge());
        if ($this->target) {
            $entry_repo->setCreatedAt($this->target->created_at);
            $entry_repo->setSourceType($this->getSourceType());
            $entry_repo->setSourceId($this->target->id);
        }
        $payer = $this->paymentLink->getPayer();
        if (empty($payer)) {
            $payer = $this->payment->payable->getUserProfile();
        }
        if ($payer instanceof Profile) {
            $entry_repo->setParty($payer);
        }
        $entry_repo->setPaymentMethod($this->payment->paymentDetails->last()->readable_method)
            ->setPaymentId($this->payment->id)
            ->setIsPaymentLink(1)->setIsDueTrackerPaymentLink($this->paymentLink->isDueTrackerPaymentLink());
        if ($this->target instanceof PosOrder) {
            $entry_repo->setIsWebstoreOrder($this->target->sales_channel == SalesChannels::WEBSTORE ? 1 : 0);
            $entry_repo->updateFromSrc();
        } else {
            $entry_repo->store();
        }
    }
    private function getSourceType(){
        if ($this->target instanceof PosOrder) return 'PosOrder';
        if ($this->target instanceof ExternalPayment) return 'ExternalPayment';
        return null;
    }

    private function notify()
    {
        if ($this->target) {
            $payment      = $this->payment;
            $payment_link = $this->paymentLink;
            dispatch(new SendPaymentLinkSms($payment, $payment_link));
            $this->notifyManager($this->payment, $this->paymentLink);
        }
    }

    private function dispatchReward()
    {
        $payable = $this->payment->payable;
        app(ActionRewardDispatcher::class)->run('payment_link_usage', $this->payment_receiver, $this->payment_receiver, $payable);
    }

    /**
     * @return PaymentLinkTransformer
     */
    private function getPaymentLink()
    {
        try {
            return $this->paymentLinkRepository->find($this->payment->payable->type_id);
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
        $recharge_transaction      = $walletTransactionHandler->setType(Types::credit())->setAmount($recharge_wallet_amount)->setSource(TransactionSources::PAYMENT_LINK)->setTransactionDetails($this->payment->getShebaTransaction()->toArray())->setLog($recharge_log)->store();
        $interest                  = (double)$this->paymentLink->getInterest();
        if ($interest > 0) {
            $formatted_interest = number_format($interest, 2);
            $log                = "$formatted_interest TK has been charged as emi interest fees against of Transc ID {$recharge_transaction->id}, and Transc amount $formatted_recharge_amount";
            $walletTransactionHandler->setLog($log)->setType(Types::debit())->setAmount($interest)->setTransactionDetails([])->setSource(TransactionSources::PAYMENT_LINK)->store();
        }

        $minus_wallet_amount       = $this->getPaymentLinkFee($recharge_wallet_amount);
        $formatted_minus_amount    = number_format($minus_wallet_amount, 2);
        $minus_log                 = "(3TK + $this->paymentLinkCommission%) $formatted_minus_amount TK has been charged as link service fees against of Transc ID: {$recharge_transaction->id}, and Transc amount: $formatted_recharge_amount";
        $walletTransactionHandler->setLog($minus_log)->setType(Types::debit())->setAmount($minus_wallet_amount)->setTransactionDetails([])->setSource(TransactionSources::PAYMENT_LINK)->store();
        /*$payment_receiver->minusWallet($minus_wallet_amount, ['log' => $minus_log]);*/

    }

    private function getPaymentLinkFee($amount)
    {
        return ($this->paymentLink->getEmiMonth() > 0 ? $this->paymentLink->getBankTransactionCharge() ?: 0 : round(($amount * $this->paymentLinkCommission) / 100, 2)) + 3;
    }

    private function clearTarget()
    {
        $this->target = $this->paymentLink->getTarget();
        if ($this->target instanceof PosOrderObject) {
            $payment_data    = [
                'pos_order_id' => $this->target->getId(),
                'amount'       => $this->payment->payable->amount,
                'method'       => $this->payment->payable->type,
                'emi_month'    => $this->payment->payable->emi_month,
                'interest'     => $this->paymentLink->getInterest(),
            ];
            /** @var PaymentCreator $payment_creator */
            $payment_creator = app(PaymentCreator::class);
            $payment_creator->credit($payment_data, $this->target->getType());
        }
        if ($this->target instanceof ExternalPayment) {
            $this->target->payment_id = $this->payment->id;
            $this->target->update();
            $this->paymentLinkRepository->statusUpdate($this->paymentLink->getLinkID(), 0);
        }
    }

    private function createUsage($payment_receiver, $modifier)
    {
        (new Usage())->setUser($payment_receiver)->setType(Usage::Partner()::PAYMENT_LINK)->create($modifier);
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
     * @param Payment                $payment
     * @param PaymentLinkTransformer $payment_link
     */
    private function notifyManager(Payment $payment, PaymentLinkTransformer $payment_link)
    {
        $partner          = $payment_link->getPaymentReceiver();
        $topic            = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel          = config('sheba.push_notification_channel_name.manager');
        $sound            = config('sheba.push_notification_sound.manager');
        $formatted_amount = number_format($payment_link->getAmount(), 2);
        $event_type       = $this->target && $this->target instanceof PosOrderObject && $this->target->getSalesChannel() == SalesChannels::WEBSTORE ? 'WebstoreOrder' : (class_basename($this->target) instanceof PosOrderObject ? 'PosOrder' : class_basename($this->target));
        (new PushNotificationHandler())->send([
            "title"      => 'Order Successful',
            "message"    => "$formatted_amount Tk has been collected from {$payment_link->getPayer()->name} by order link- {$payment_link->getLinkID()}",
            "event_type" => $event_type,
            "event_id"   => $this->target->getId(),
            "sound"      => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);
    }
}
