<?php

namespace App\Jobs;
use App\Repositories\SmsHandler;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\FraudDetection\TransactionSources;
use Sheba\PartnerWallet\PartnerTransactionHandler;
use Sheba\Transactions\Wallet\WalletTransactionHandler;


class SendToCustomerToInformDueDepositSMS extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $data;
    private $partner;
    public function __construct($partner,$data)
    {
        $this->data = $data;
        $this->partner = $partner;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     */
    public function handle()
    {
        try {
            list($sms, $log) = $this->getSms();
            $sms_cost = $sms->getCost();
            if ((double)$this->partner->wallet > (double)$sms_cost) {
                /** @var WalletTransactionHandler $walletTransactionHandler */
                $sms->shoot();
                (new WalletTransactionHandler())->setModel($this->partner)->setAmount($sms_cost)->setType('debit')->setLog($sms_cost . $log)->setTransactionDetails([])->setSource(TransactionSources::SMS)->store();
            }

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }

    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getSms()
    {

        if ($this->data['type'] == 'due') {
            $sms = (new SmsHandlerRepo('inform-due'))->setVendor('infobip')->setMobile($this->data['mobile'])->setMessage([
                'customer_name' => $this->data['customer_name'],
                'partner_name' => $this->data['partner_name'],
                'amount' => $this->data['amount'],
                'payment_link' => $this->data['payment_link']
            ]);
            $log = " BDT has been deducted for sending due details";
        } else {
            $sms = (new SmsHandlerRepo('inform-deposit'))->setVendor('infobip')->setMobile($this->data['mobile'])->setMessage([
                'customer_name' => $this->data['customer_name'],
                'partner_name' => $this->data['partner_name'],
                'amount' => $this->data['amount'],
            ]);
            $log = " BDT has been deducted for sending deposit details";
        }
        return [$sms, $log];
    }
}
