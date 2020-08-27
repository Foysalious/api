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
        #$business_email = $this->procurement->owner->getContactEmail();
        $business_email = 'ffaahhiimm15@gmail.com';
        $business_contract_person = $this->procurement->owner->getContactPerson();
        $tender_detail = config('sheba.business_url') . "/dashboard/rfq/list/" . $this->procurement->id."/details";
        $portal_link = config('sheba.business_url');
        Mail::send('emails.tender-publication', ['business_contract_person' => $business_contract_person, 'tender_detail' => $tender_detail, 'portal_link' => $portal_link], function ($m) use ($business_email){
            $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
            $m->to($business_email)->subject('Your tender has been published successfully');
        });
    }
}