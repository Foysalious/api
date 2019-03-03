<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;
use Mail;
class SendEmailVerficationEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $customer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $verfication_code = str_random(30);
        Redis::set('email-verification-' . $this->customer->id, $verfication_code);
        Redis::expire('email-verification-' . $this->customer->id, 30 * 60);
        Mail::send('emails.email-verification', ['customer' => $this->customer, 'code' => $verfication_code], function ($m) {
            $m->to($this->customer->email)->subject('Email Verification');
        });
    }
}
