<?php namespace App\Jobs\Business;

use App\Models\Business;
use App\Sheba\Business\BusinessEmailQueue;
use Exception;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTopUpFailMail extends BusinessEmailQueue
{
    use InteractsWithQueue, SerializesModels;

    private $email;
    private $file;
    private $business;

    /**
     * SendTenderBillInvoiceEmailToBusiness constructor.
     * @param Business $business
     * @param $email
     * @param $file
     */
    public function __construct(Business $business, $email, $file)
    {
        $this->email = $email;
        $this->file = $file;
        $this->business = $business;
        parent::__construct();
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
            $subject = 'Error in Bulk CSV upload for Bulk Top-Up request';
            Mail::send('emails.topup-fail-email', [
                'report_file' => $this->file, 'business_name' => $this->business->name
            ], function ($m) use ($subject) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($this->email)->subject($subject);
                $m->attach($this->file);
            });
        }
    }
}
