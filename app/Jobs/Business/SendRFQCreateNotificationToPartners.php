<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use App\Models\Partner;
use App\Models\Procurement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Notification\Partner\PartnerNotificationHandler;

class SendRFQCreateNotificationToPartners extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /** @var Procurement $procurement */
    private $procurement;
    /** @var PartnerNotificationHandler $partnerNotificationHandler */
    private $partnerNotificationHandler;

    /**
     * SendRFQCreateNotificationToPartners constructor.
     * @param Procurement $procurement
     * @param PartnerNotificationHandler $partner_notification_handler
     */
    public function __construct(Procurement $procurement, PartnerNotificationHandler $partner_notification_handler)
    {
        $this->procurement = $procurement;
        $this->partnerNotificationHandler = $partner_notification_handler;
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $title = $this->procurement->title ? $this->procurement->title : substr($this->procurement->long_description, 0, 20);
            $long_description = substr($this->procurement->long_description, 0, 50);
            $link = config('sheba.business_url') . "/tender/list/" . $this->procurement->id;
            $partner_ids = Partner::verified()->pluck('id')->toArray();

            $this->partnerNotificationHandler->setTitle($title)
                ->setDescription($long_description)
                ->setLink($link)
                ->notifyForProcurement($partner_ids);
        }
    }
}
