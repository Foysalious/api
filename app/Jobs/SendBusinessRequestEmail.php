<?php namespace App\Jobs;

use App\Sheba\Business\BusinessEmailQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBusinessRequestEmail extends BusinessEmailQueue
{
    use InteractsWithQueue, SerializesModels;

    private $email, $password, $template, $subject;

    /**
     * SendBusinessRequestEmail constructor.
     * @param $email
     */
    public function __construct($email)
    {
        $this->email = $email;
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() <= 2) {
            $template = $this->template ?: 'emails.profile-creation';
            $subject = $this->subject ?: 'Profile Creation';
            $email = $this->email;

            Mail::send($template, ['email' => $this->email, 'password' => $this->password], function ($m) use ($subject, $email) {
                $m->from('noreply@sheba-business.com', 'Sheba Platform Limited');
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
