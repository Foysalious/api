<?php namespace App\Jobs;

use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\Sms\Sms;

class TestSmsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Sms $sms)
    {
        /*Mail::raw('Text to e-mail', function ($m) {
            $m->from('hello@app.com', 'Your Application');
            $m->to('arnabrahman@hotmail.com', 'Arnab')->subject('Server!');
        });*/

        $sms
            ->setFeatureType(FeatureType::COMMON)
            ->setBusinessType(BusinessType::COMMON)
            ->shoot('+8801678242973', 'Test job from queue with supervisor at ' . \Carbon\Carbon::now()->toDateTimeString());
    }
}
