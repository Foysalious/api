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
    private $data;

    /**
     * SendTenderBillInvoiceEmailToBusiness constructor.
     * @param $email
     * @param $file
     * @param array $data
     */
    public function __construct($email, $file, array $data)
    {
        $this->email = $email;
        $this->file = $file;
        $this->data = $data;
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
            $subject = $this->data['subject'];
            Mail::send('emails.tender_bill_invoice', [
                'super_admin_name' => $this->data['super_admin_name'],
                'order_id' => $this->data['order_id'],
                'type' => $this->data['type'],
                'url' => $this->data['url']
            ], function ($m) use ($subject) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($this->email)->subject($subject);
                $m->attach($this->file);
            });
        }
    }
}
