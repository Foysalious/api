<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use App\Models\Bid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendEmailForBiddingTenderToBusiness extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /** @var Bid $bid */
    private $bid;
    private $procurement;

    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
        $this->procurement = $bid->procurement;
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $business_email = $this->procurement->owner->getContactEmail();
            $business_contract_person = $this->procurement->owner->getContactPerson();
            $tender_id = $this->procurement->id;
            $vendor_name = $this->bid->bidder->name;
            $bid_detail = config('sheba.business_url') . "/dashboard/rfq/list/" . $tender_id . "/biddings/" . $this->bid->id;
            $subject = "$vendor_name participated in your tender $tender_id";

            Mail::send('emails.tender-bidding', [
                'business_contract_person' => $business_contract_person, 'vendor_name' => $vendor_name, 'bid_detail' => $bid_detail, 'tender_id' => $tender_id
            ], function ($m) use ($subject, $business_email) {
                $m->from('b2b@sheba.xyz', 'sBusiness.xyz');
                $m->to($business_email)->subject($subject);
            });
        }
    }
}
