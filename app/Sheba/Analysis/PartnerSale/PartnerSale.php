<?php namespace Sheba\Analysis\PartnerSale;

use App\Models\Partner;
use Illuminate\Support\Collection;
use Sheba\Helpers\TimeFrame;

abstract class PartnerSale
{
    const DAY_BASE = "day";
    const WEEK_BASE = "week";
    const MONTH_BASE = "month";
    const YEAR_BASE = "year";

    /** @var TimeFrame */
    protected $timeFrame;
    /** @var Partner */
    private $partner;
    /** @var PartnerSale */
    protected $next;

    protected $frequency;

    public function __construct(PartnerSale $next = null)
    {
        $this->next = $next;
    }

    public function setParams($frequency = 'day')
    {
        if(!in_array($frequency, ['day', 'week', 'month', 'year'])) throw new \Exception('Invalid frequency');
        $this->frequency = $frequency;
        return $this;
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

    /**
     * @return Collection
     */
    public function get()
    {
        return $this->calculate();
    }

    /**
     * @return Collection
     */
    protected abstract function calculate();
}