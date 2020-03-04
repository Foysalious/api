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


    private $vendor;

    /**
     * SendEmailToNotifyVendorBalance constructor.
     * @param $vendor
     */
    public function __construct($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        try {
            $balance = $this->vendor->balance();
            if ($balance < config('ticket.balance_threshold')) {
                $users = $this->notifiableUsers();
                foreach ($users as $user) {

                    $mailer->send('emails.notify-vendor-balance', ['current_balance' => $balance, 'vendor_name' => (new \ReflectionClass($this->vendor))->getShortName()], function ($m) use ($user) {
                        $m->from('yourEmail@domain.com', 'Sheba.xyz');
                        $m->to($user->email)->subject('Low Balance for ' . (new \ReflectionClass($this->vendor))->getShortName());
                    });
                }
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }

    }

    private function notifiableUsers()
    {
        return User::whereIn('id',config('ticket.notifiable_users'))->get();
    }
}
