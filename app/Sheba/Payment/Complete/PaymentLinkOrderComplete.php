<?php namespace Sheba\Payment\Complete;

use App\Jobs\Partner\PaymentLink\SendPaymentLinkSms;
use App\Models\Payable;
use App\Models\Payment;
use App\Models\PosOrder;
use App\Models\Profile;
use DB;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\Dal\ExternalPayment\Model as ExternalPayment;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\ModificationFields;
use Sheba\PaymentLink\InvoiceCreator;
use Sheba\PaymentLink\PaymentLinkStatics;
use Sheba\PaymentLink\PaymentLinkTransaction;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;
use Sheba\Repositories\PaymentLinkRepository;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Usage\Usage;

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
    private $paymentLinkTax;
    /**
     * @var int
     */
    private $entryAmount = 0;

    public function __construct()
    {
        parent::__construct();
        $this->paymentLinkRepository = app(PaymentLinkRepositoryInterface::class);
        $this->invoiceCreator        = app(InvoiceCreator::class);
        $this->paymentLinkCommission = PaymentLinkStatics::get_payment_link_commission();
        $this->paymentLinkTax        = PaymentLinkStatics::get_payment_link_tax();
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
        } catch (\Throwable $e) {
            logError($e);
        }
        return $this->payment;
    }

    private function storeEntry()
    {

        $payable = $this->payment->payable;
        /** @var AutomaticEntryRepository $entry_repo */
        $entry_repo = app(AutomaticEntryRepository::class)->setPartner($this->payment_receiver)->setAmount($this->entryAmount)->setHead(AutomaticIncomes::PAYMENT_LINK)
                                                          ->setEmiMonth($payable->emi_month)->setAmountCleared($this->entryAmount);
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

    private function getSourceType()
    {
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
        $transaction       = (new PaymentLinkTransaction($this->payment, $this->paymentLink))->setReceiver($payment_receiver)->create();
        $this->entryAmount = $this->paymentLink->getPaidBy() == PaymentLinkStatics::paidByTypes()[0] ? $transaction->getAmount() : $transaction->getAmount() - $transaction->getFee() - $this->paymentLink->getPartnerProfit();
    }

    private function getPaymentLinkFee($amount)
    {
        return ($this->paymentLink->getEmiMonth() > 0 ? $this->paymentLink->getBankTransactionCharge() ?: 0 : round(($amount * $this->paymentLinkCommission) / 100, 2)) + 3;
    }

    private function clearTarget()
    {
        $this->target = $this->paymentLink->getTarget();
        if ($this->target instanceof PosOrder) {
            $payment_data    = [
                'pos_order_id' => $this->target->id,
                'amount'       => $this->payment->payable->amount,
                'method'       => $this->payment->payable->type,
                'emi_month'    => $this->payment->payable->emi_month,
                'interest'     => $this->paymentLink->getInterest(),
            ];
            $payment_creator = app(PaymentCreator::class);
            $payment_creator->credit($payment_data);
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
        $event_type       = $this->target && $this->target instanceof PosOrder && $this->target->sales_channel == SalesChannels::WEBSTORE ? 'WebstoreOrder' : class_basename($this->target);
        /** @var Payable $payable */
        $payable = Payable::find($this->payment->payable_id);
        (new PushNotificationHandler())->send([
            "title"      => 'Order Successful',
            "message"    => "$formatted_amount Tk has been collected from {$payable->getName() } by order link- {$payment_link->getLinkID()}",
            "event_type" => $event_type,
            "event_id"   => $this->target->id,
            "sound"      => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);
    }
}
