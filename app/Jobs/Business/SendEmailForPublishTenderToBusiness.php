<?php namespace App\Jobs\Business;

use App\Models\Procurement;
use App\Sheba\Business\BusinessEmailQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Mail\BusinessMail;

class SendEmailForPublishTenderToBusiness extends BusinessEmailQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var Procurement $procurement */
    private $procurement;

    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $business_email = $this->procurement->owner->getContactEmail();
            $business_contract_person = $this->procurement->owner->getContactPerson();
            $tender_detail = config('sheba.business_url') . "/dashboard/rfq/list/" . $this->procurement->id . "/details";
            $public_tender_detail = config('sheba.business_url') . "/tender/list/" . $this->procurement->id;

            BusinessMail::send('emails.tender-publication', [
                'business_contract_person' => $business_contract_person, 'tender_detail' => $tender_detail, 'public_tender_detail' => $public_tender_detail
            ], function ($m) use ($business_email) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($business_email)->subject('Your tender has been published successfully');
            });
        }
    }
}
