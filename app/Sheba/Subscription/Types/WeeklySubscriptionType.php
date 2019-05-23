<?php


namespace Sheba\Subscription\Types;


use Carbon\Carbon;

class WeeklySubscriptionType extends SubscriptionType
{
    private $currentDayName;

    public function __construct()
    {
        parent::__construct();
        $this->currentDayName = date('l');
    }

    /**
     * @return Carbon[]
     */
    public function getDates()
    {

        $this->sortDays();
        if ($today = $this->getToday()) array_push($this->dates, $this->addTime(Carbon::now()));
        $to_date = $this->addTime($this->toDate);
        while ($to_date > $this->addTime($this->currentDate)) {
            foreach ($this->values as $value) {
                $inspection_date = $this->addTime(Carbon::parse('next ' . $value['day']));
                $this->currentDate = $inspection_date;
                Carbon::setTestNow($this->currentDate);
                if ($this->toDate > $inspection_date) array_push($this->dates, $inspection_date);
            }
        }
        return $this->dates;
    }

    private function sortDays()
    {
        $weeks = constants('WEEKS');
        $final = collect();
        foreach ($this->values as $day_name) {
            $final->push(['value' => $weeks[ucfirst($day_name)], 'day' => $day_name]);
        }
        $this->values = $final->sortBy('value');
    }

    private function getToday()
    {
        return $this->values->filter(function ($day_name) {
            return $day_name['day'] == $this->currentDayName;
        })->first();
    }
}