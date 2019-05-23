<?php namespace Sheba\Subscription\Types;


use Carbon\Carbon;
use Illuminate\Support\Collection;

abstract class SubscriptionType
{
    /** @var Collection $values */
    protected $values;
    /** @var Carbon $currentDate */
    protected $currentDate;
    /** @var Carbon $toDate */
    protected $toDate;
    protected $currentMonth;
    protected $currentYear;
    protected $currentDay;
    /** @var Carbon[] $dates */
    protected $dates;

    public function __construct()
    {
        $this->currentDate = Carbon::now();
        $this->currentMonth = (int)date('m');
        $this->currentYear = (int)date('Y');
        $this->currentDay = (int)date('d');
        $this->dates = [];
    }

    /**
     * @return Carbon[]
     */
    abstract public function getDates();

    public function setValues($values)
    {
        $this->values = collect($values);
        return $this;
    }

    public function seToDate(Carbon $date)
    {
        $this->toDate = $date;
        return $this;
    }
}