<?php namespace App\Jobs\Business;

use App\Models\Profile;
use App\Sheba\Business\BusinessEmailQueue;
use Exception;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Sheba\Mail\BusinessMail;

class SendMailVerificationCodeEmail extends BusinessEmailQueue
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
            $verification_code = randomString(4, true);
            $key_name = 'email_verification_code_' . $verification_code;
            Redis::set($key_name, json_encode(["profile_id" => $this->profile->id, 'code' => $verification_code]));
            Redis::expire($key_name, 600);

            $email = $this->profile->email;
            $subject = $verification_code . " is sBusiness login code";

            BusinessMail::send('emails.email_verification_V3', ['code' => $verification_code], function ($m) use ($email, $subject) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($email)->subject($subject);
            });
        }
    }
}
