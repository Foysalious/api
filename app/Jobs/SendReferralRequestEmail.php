<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Customer;
use App\Models\Voucher;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReferralRequestEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $customer;
    private $voucher;
    private $email;

    /**
     * Create a new job instance.
     *
     * @param Customer $customer
     * @param Voucher $voucher
     */
    public function __construct(Customer $customer, $email, Voucher $voucher)
    {
        $this->customer = $customer;
        $this->voucher = $voucher;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $mailer->send('emails.referral-request', ['customer' => $this->customer, 'voucher' => $this->voucher, 'front' => env('SHEBA_FRONT_END_URL')], function ($m) {
            $m->from('mail@sheba.xyz', 'Sheba.xyz');
            $m->to($this->email)->subject('Referral Request');
        });
    }
}
