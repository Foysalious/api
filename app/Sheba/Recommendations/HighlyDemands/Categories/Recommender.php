<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

use App\Models\Location;
use Carbon\Carbon;
use Sheba\Helpers\TimeFrame;

abstract class Recommender
{
    /** @var null */
    protected $month;
    /** @var null */
    protected $year;
    /** @var null */
    protected $day;
    protected $next;
    /** @var TimeFrame $timeFrame */
    protected $timeFrame;
    protected $locationId;

    public function __construct(Recommender $next = null)
    {
        $this->next = $next;
        $this->timeFrame = new TimeFrame();
    }

    public function setParams(Carbon $date)
    {
        $this->year = $date->year;
        $this->month = $date->month;
        $this->day = $date->day;

        return $this;
    }

    public function setLocationId($location_id)
    {
        $this->locationId = $location_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->recommendation();
    }

    abstract protected function recommendation();
}
