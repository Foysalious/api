<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use App\Models\Profile;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;

class SendMailVerificationCodeEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $profile;

    /**
     * Create a new job instance.
     *
     * @param $profile
     */
    public function __construct($profile)
    {
        $this->profile = $profile instanceof Profile ? $profile : Profile::find($profile);
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
            $verification_code = randomString(4, 1);
            $key_name = 'email_verification_code_' . $verification_code;
            #Redis::set($key_name, json_encode(["profile_id" => 258042, 'code' => $verification_code]));
            #Redis::expire($key_name, 600);
            $email = 'miajee@sheba.xyz';

            $subject = $verification_code . " is sBusiness login code";
            Mail::send('emails.email_verification_V3', ['code' => 12211221], function ($m) use ($email, $subject) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($email)->subject($subject);
            });
        }
    }
}
