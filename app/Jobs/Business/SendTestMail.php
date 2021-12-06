<?php namespace App\Jobs\Business;

use App\Sheba\Business\BusinessEmailQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Mail\BusinessMail;

class SendTestMail extends BusinessEmailQueue
{
    use InteractsWithQueue, SerializesModels;

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $email = request()->email;
            $subject = "This Is Test Mail";
            BusinessMail::send('emails.test-mail', [], function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject);
            });
        }
    }
}
