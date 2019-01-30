<?php namespace App\Jobs;

use App\Http\Requests\Request;
use App\Jobs\Job;
use App\Library\Sms;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class TestJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /*Mail::raw('Text to e-mail', function ($m) {
            $m->from('hello@app.com', 'Your Application');
            $m->to('arnabrahman@hotmail.com', 'Arnab')->subject('Server!');
        });*/
        Sms::send_single_message('+8801678242973', 'Test job from queue with supervisor at ' . \Carbon\Carbon::now()->toDateTimeString());
    }
}
