<?php

namespace App\Jobs;
use App\Repositories\SmsHandler;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\PartnerWallet\PartnerTransactionHandler;


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
            $message_data = [
                'customer_name' => $this->data['customer_name'],
                'partner_name' => $this->data['partner_name'],
                'amount' => $this->data['amount'],
            ];

            if ($this->data['type'] == 'due') {
                $sms_handler = (new SmsHandler('inform-due'));
                $message_data['payment_link'] = $this->data['payment_link'];
                $log = " BDT has been deducted for sending due details";
            } else {
                $sms_handler = (new SmsHandler('inform-deposit'));
                $log = " BDT has been deducted for sending deposit details";
            }

            $sms = $sms_handler
                ->setBusinessType(BusinessType::SMANAGER)
                ->setFeatureType(FeatureType::DUE_TRACKER)
                ->send($this->data['mobile'], $message_data);

            // TODO
            $sms_cost = $sms->getCost();
            $partner_transaction_handler = new PartnerTransactionHandler($this->partner);
            $partner_transaction_handler->debit($sms_cost, $sms_cost . $log, null, null);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }

    }
}
