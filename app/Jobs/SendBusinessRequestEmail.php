<?php namespace App\Jobs;

use App\Exceptions\MailgunClientException;
use Exception;
use App\Sheba\Business\BusinessEmailQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Mail\BusinessMail;

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
     * @throws MailgunClientException
     */
    public function handle()
    {
        if ($this->attempts() > 1) return;

        $template = $this->template ?: 'emails.profile-creation';
        $subject = $this->subject ?: 'Profile Creation';
        $email = $this->email;

        try {
            BusinessMail::send($template, ['email' => $email, 'password' => $this->password], function ($m) use ($subject, $email) {
                $m->from('noreply@sheba-business.com', 'Sheba Platform Limited');
                $m->to($email)->subject($subject);
            });
        } catch (Exception $exception) {
            throw new MailgunClientException();
        }
    }

    /**
     * @param mixed $password
     * @return SendBusinessRequestEmail
     */
    public function setPassword($password): SendBusinessRequestEmail
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param mixed $template
     * @return SendBusinessRequestEmail
     */
    public function setTemplate($template): SendBusinessRequestEmail
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @param mixed $subject
     * @return SendBusinessRequestEmail
     */
    public function setSubject($subject): SendBusinessRequestEmail
    {
        $this->subject = $subject;
        return $this;
    }
}
