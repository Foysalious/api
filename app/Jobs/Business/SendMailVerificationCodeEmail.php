<?php namespace App\Jobs\Business;


use App\Jobs\Job;
use App\Models\Profile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

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
     * @throws \Exception
     */
    public function handle()
    {
        #$verification_code = randomString(6, 1);
        $verification_code = 1111;
        $key_name = 'email_verification_code_' . $verification_code;
        Redis::set($key_name, json_encode([
            "profile_id" => $this->profile->id,
            'code' => $verification_code
        ]));
        Redis::expire($key_name, 600);
        Mail::send('emails.email_verification_V3', ['code' => $verification_code], function ($m) {
            $m->to('miajee@sheba.xyz')->subject('Email Verification');
        });
    }
}