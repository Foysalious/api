<?php

namespace App\Jobs;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBusinessRequestEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $email, $password, $template, $subject;

    public function __construct($email)
    {
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
        if ($this->attempts() <= 2) {
            $template = $this->template ?: 'emails.profile-creation';
            $subject = $this->subject ?: 'Profile Creation';
            $email = $this->email;
            $mailer->send($template, ['email' => $this->email, 'password' => $this->password], function ($m) use ($subject, $email) {
                $m->from('mail@sheba.xyz', 'Sheba.xyz');
                $m->name('Sheba Accounts');
                $m->to($email)->subject($subject);
            });
        }
    }

    /**
     * @param mixed $password
     * @return SendBusinessRequestEmail
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param mixed $template
     * @return SendBusinessRequestEmail
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param mixed $subject
     * @return SendBusinessRequestEmail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
}
