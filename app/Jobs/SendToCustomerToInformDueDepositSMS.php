<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\SmsHandler;


class SendToCustomerToInformDueDepositSMS extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->data['type'] == 'due') {
            (new SmsHandler('inform-due'))->send($this->data['mobile'], [
                'customer_name' => $this->data['customer_name'],
                'partner_name' => $this->data['partner_name'],
                'amount' => $this->data['amount'],
                'payment_link' => $this->data['payment_link']
            ]);
        } else {
            (new SmsHandler('inform-deposit'))->send($this->data['mobile'], [
                'customer_name' => $this->data['customer_name'],
                'partner_name' => $this->data['partner_name'],
                'amount' => $this->data['amount'],
            ]);
        }

    }
}
