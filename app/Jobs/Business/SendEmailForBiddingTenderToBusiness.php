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
        $business_name = $this->procurement->owner->name;
        #$business_email = $this->procurement->owner->getContactEmail();
        $tender_id = $this->procurement->id;
        $vendor_name = $this->bid->bidder->name;
        $bid_detail = config('sheba.business_url') . "/dashboard/rfq/list/" . $tender_id . "/biddings/" . $this->bid->id;
        $subject = "$vendor_name participated in your tender $tender_id";
        Mail::send('emails.tender-bidding', ['business_name' => $business_name, 'vendor_name' => $vendor_name, 'bid_detail' => $bid_detail, 'tender_id' => $tender_id], function ($m) use ($subject) {
            $m->to('ffaahhiimm15@gmail.com')->subject($subject);
        });
    }
}