<?php namespace App\Jobs\Business;

use App\Sheba\Business\BusinessEmailQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Mail\BusinessMail;

class SendBulkAttendanceReconciliationErrorEmail extends BusinessEmailQueue
{
    use InteractsWithQueue, SerializesModels;

    private $businessMember;
    private $excelErrorFile;

    public function __construct($business_member, $excel_error_file)
    {
        $this->businessMember = $business_member;
        $this->excelErrorFile = $excel_error_file;
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $subject = 'Error Report for Bulk Attendance excel upload request';
            $profile = $this->businessMember->member->profile;
            BusinessMail::send('emails.attendance-reconciliation-error', [
                'title' => $subject,
                'name' => $profile->name
            ], function ($m) use ($subject, $profile) {
                $m->from('noreply@sheba-business.com', 'Sheba Platform Limited');
                $m->to($profile->email)->subject($subject);
                $m->attach($this->excelErrorFile);
            });
        }
    }

}