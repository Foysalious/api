<?php

namespace App\Jobs\Partner\WalletRecharge;

use App\Jobs\Job;
use App\Models\Partner;
use App\Sheba\DueTracker\Exceptions\InsufficientBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Sheba\Sms\Sms;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class SendSmsOnWalletRecharge extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $partner;
    private $message;

    /**
     * SendSmsOnWalletRecharge constructor.
     * @param Partner $partner
     * @param $message
     */
    public function __construct(Partner $partner, $message)
    {
        $this->partner = $partner;
        $this->message = $message;
    }

    public function handle()
    {
        if ($this->attempts() > 2) return;

        $sms = (new Sms())
            ->to($this->partner->mobile)
            ->msg($this->message)
            ->setFeatureType(FeatureType::PAYMENT)
            ->setBusinessType(BusinessType::SMANAGER);

        $sms_cost = $sms->estimateCharge();
        if ((double)$this->partner->wallet < (double)$sms_cost->getTotalCharge()) throw new InsufficientBalance();
        $sms->shoot();
        (new WalletTransactionHandler())
            ->setModel($this->partner)
            ->setAmount($sms_cost->getTotalCharge())
            ->setType(Types::debit())
            ->setLog((string) $sms_cost->getTotalCharge() . " BDT has been deducted for sending recharge complete SMS")
            ->setTransactionDetails([])
            ->setSource(TransactionSources::SMS)
            ->store();
    }
}