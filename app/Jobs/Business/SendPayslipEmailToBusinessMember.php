<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPayslipEmailToBusinessMember extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $email;
    private $payslipPdfFile;
    private $businessMember;
    private $timePeriod;
    private $profile;

    public function __construct($business_member, $time_period, $payslip_pdf_file)
    {
        $this->businessMember = $business_member;
        $this->profile = $business_member->member->profile;
        $this->timePeriod = $time_period;
        $this->payslipPdfFile = $payslip_pdf_file;
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $subject = 'Payslip of '.$this->timePeriod->start->format('F Y');
            Mail::send('emails.payslip-email', [
                'title' => $subject,
                'employee_name' => $this->profile->name,
                'time_period' => $this->timePeriod->start->format('F Y'),
                'business_name' => $this->businessMember->business->name
            ], function ($m) use ($subject) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($this->profile->email)->subject($subject);
                $m->attach($this->payslipPdfFile);
            });
        }
    }
}