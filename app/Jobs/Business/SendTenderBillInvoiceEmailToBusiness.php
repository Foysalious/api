<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTenderBillInvoiceEmailToBusiness extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $email;
    private $file;

    /**
     * SendTenderBillInvoiceEmailToBusiness constructor.
     * @param $email
     * @param $file
     */
    public function __construct($email, $file)
    {
        $this->email = $email;
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        if ($this->attempts() <= 1) {
            $subject = 'Business Bill/Invoice Email';
            Mail::send('emails.email_verification_V3', ['code' => 56598], function ($m) use ($subject) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($this->email)->subject($subject);
                $m->attach($this->file);
            });
        }
    }
}
