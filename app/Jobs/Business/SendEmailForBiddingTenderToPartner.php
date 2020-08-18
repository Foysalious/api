<?php namespace App\Jobs\Business;


use App\Jobs\Job;
use App\Models\Procurement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailForBiddingTenderToPartner extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /** @var Procurement $procurement */
    private $procurement;

    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $link = config('sheba.business_url') . "/tender/list/" . $this->procurement->id;
        }
    }
}