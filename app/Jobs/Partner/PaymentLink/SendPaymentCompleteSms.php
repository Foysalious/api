<?php

namespace App\Jobs\Partner\PaymentLink;

use App\Jobs\Job;
use App\Models\Partner;
use App\Models\Payable;
use App\Models\Payment;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Sheba\FraudDetection\TransactionSources;
use Sheba\PaymentLink\PaymentLinkTransaction;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\Sms\Sms;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class SendPaymentCompleteSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var PaymentLinkTransformer */
    private $paymentLink;
    /** @var Payment */
    private $payment;
    /** @var PaymentLinkTransaction */
    private $transaction;

    /**
     * SendPaymentCompleteSms constructor.
     * @param Payment $payment
     * @param PaymentLinkTransformer $paymentLink
     * @param PaymentLinkTransaction $transaction
     */
    public function __construct(
        Payment $payment,
        PaymentLinkTransformer $paymentLink,
        PaymentLinkTransaction $transaction
    ) {
        $this->payment = $payment;
        $this->paymentLink = $paymentLink;
        $this->transaction = $transaction;
    }

    public function handle()
    {
        if ($this->attempts() > 2) return;

        /** @var Partner $partner */
        $partner = $this->paymentLink->getPaymentReceiver();
        /** @var Payable $payable */
        $payable = Payable::find($this->payment->payable_id);

        $formatted_amount = number_format($this->transaction->getAmount(), 2);
        $formatted_fee = number_format($this->transaction->getFee(), 2);
        $formatted_received_amount = number_format($this->paymentLink->getRealAmount(), 2);
        $payment_completion_date = Carbon::parse($this->payment->updated_at)->format('d/m/Y');

        $message = "Payment {$formatted_amount} tk from {$payable->getName()} {$payable->getMobile()} completed, Fee {$formatted_fee} tk, Received {$formatted_received_amount} tk. TrxID: 8BHSU5400  at {$payment_completion_date}. sManager (SPL Ltd.)";

        $sms = new Sms();
        $sms->to($partner->mobile)
        ->msg($message)
        ->setFeatureType(FeatureType::PAYMENT_LINK)
        ->setBusinessType(BusinessType::SMANAGER);

        $sms_cost = $sms->estimateCharge();
        if ((double)$partner->wallet < $sms_cost) throw new InsufficientBalance();
        Log::info('sending sms');
        $sms->shoot();
        Log::info('after sending sms');
        (new WalletTransactionHandler())
            ->setModel($partner)
            ->setAmount($sms_cost)
            ->setType(Types::debit())
            ->setLog((string) $sms_cost . " BDT has been deducted for sending payment link complete SMS")
            ->setTransactionDetails([])
            ->setSource(TransactionSources::SMS)
            ->store();
    }
}