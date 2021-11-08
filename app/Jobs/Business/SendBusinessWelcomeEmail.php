<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBusinessWelcomeEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $businessMember;
    private $profile;

    public function __construct($business_member)
    {
        $this->businessMember = $business_member;
        $this->profile = $business_member->member->profile;
    }
    public function handle()
    {
        if ($this->attempts() <= 1) {
            $subject = 'Welcome to sBusiness Platform';
            Mail::send('emails.business-welcome-email', [
                'title' => $subject,
                'customer_name' => $this->profile->name,
                'business_name' => $this->businessMember->business->name
            ], function ($m) use ($subject) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($this->profile->email)->subject($subject);
            });
        }
    }
}