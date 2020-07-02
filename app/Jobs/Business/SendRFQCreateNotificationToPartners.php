<?php namespace App\Jobs\Business;

use App\Jobs\Job;
use App\Models\Partner;
use App\Models\Procurement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRFQCreateNotificationToPartners extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /** @var Procurement $procurement */
    private $procurement;

    /**
     * SendRFQCreateNotificationToPartners constructor.
     * @param Procurement $procurement
     */
    public function __construct(Procurement $procurement)
    {
        $this->procurement = $procurement;
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            $message = $this->procurement->owner->name . " has created RFQ #" . $this->procurement->id;
            $partners = Partner::verified()->select('id', 'sub_domain')->get();
            foreach ($partners as $partner) {
                notify()->partner($partner)->send([
                    'title' => $message,
                    'type' => 'warning',
                    'event_type' => get_class($this->procurement),
                    'event_id' => $this->procurement->id,
                    'link' => config('sheba.partners_url') . "/" . $partner->sub_domain . "/procurements/" . $this->procurement->id
                ]);
            }
        }
    }
}
