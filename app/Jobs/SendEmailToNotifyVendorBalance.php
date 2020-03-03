<?php

namespace App\Jobs;

use App\Jobs\Job;

use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailToNotifyVendorBalance extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;


    private $currentBalance,$vendorName;

    public function __construct($current_balance, $vendor_name)
    {
        $this->currentBalance = $current_balance;
        $this->vendorName = $vendor_name;
    }

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $users = $this->notifiableUsers();
        foreach($users as $user){

            $mailer->send('emails.notify-vendor-balance', ['current_balance' => $this->currentBalance,'vendor_name' => $this->vendorName ], function ($m) use($user) {
                $m->from('yourEmail@domain.com', 'Sheba.xyz');
                $m->to($user->email)->subject('Low Balance for '.$this->vendorName);
            });

        }

    }

    private function notifiableUsers()
    {
        return User::whereIn('id',config('ticket.notifiable_users'))->get();
    }
}
