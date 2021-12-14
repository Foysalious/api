<?php namespace App\Sheba\WebstoreBanner\Events;
use App\Events\Event;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner;

class WebstoreBannerUpdate extends Event
{
    use SerializesModels, DispatchesJobs;

    /**
     * @var PartnerWebstoreBanner
     */
    private $webstoreBaneer;
    private $banner;
    private $partnerId;

    public function getPartnerId()
    {
        return $this->partnerId;
    }
    public function __construct(PartnerWebstoreBanner $webstoreBaneer)
    {
        $this->partnerId = $webstoreBaneer->partner->id;
    }
}
