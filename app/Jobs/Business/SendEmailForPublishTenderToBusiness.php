<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use App\Models\Procurement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendEmailForPublishTenderToBusiness extends Job implements ShouldQueue
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
        $business_name = $this->procurement->owner->name;
        #$business_email = $this->procurement->owner->getContactEmail();
        $tender_detail = config('sheba.business_url') . "/dashboard/rfq/list/" . $this->procurement->id."/details";
        $portal_link = config('sheba.business_url');
        Mail::send('emails.tender-publication', ['business_name' => $business_name, 'tender_detail' => $tender_detail, 'portal_link' => $portal_link], function ($m) {
            $m->to('miajee@sheba.xyz')->subject('Your tender has been published successfully');
        });
    }
}