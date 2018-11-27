<?php namespace Sheba\Analysis\PartnerPerformance;

use App\Models\Partner;
use Illuminate\Support\Collection;
use Sheba\Helpers\TimeFrame;

abstract class PartnerPerformance
{
    /** @var TimeFrame */
    private $timeFrame;

    /** @var Partner */
    private $partner;

    /** @var PartnerPerformance  */
    protected $next;

    /** @var Collection */
    private $data;

    public function __construct(PartnerPerformance $next = null)
    {
        $this->next = $next;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function calculate()
    {
        $this->data = $this->get();
    }

    /**
     * @return Collection
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Collection
     */
    protected abstract function get();
}