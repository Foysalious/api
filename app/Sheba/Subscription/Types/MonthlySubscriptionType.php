<?php namespace Sheba\Subscription\Types;


use Carbon\Carbon;

class MonthlySubscriptionType extends SubscriptionType
{
    /**
     * @return Carbon[]
     */
    public function getDates()
    {
        $this->values = $this->values->sort()->unique();
        $month = $this->currentMonth;
        $year = $this->currentYear;
        while ($this->toDate > $this->addTime(Carbon::createFromDate($year, $month, $this->values->first()))) {
            foreach ($this->values as $date) {
                $inspection_date = $this->addTime(Carbon::createFromDate($year, $month, $date));
                while ((int)$inspection_date->format('m') != $month && (int)$inspection_date->format('d') != $date) {
                    $inspection_date = $this->addTime(Carbon::createFromDate($year, $month, $date - 1));
                }
                if ($this->toDate > $inspection_date && $this->currentDate < $inspection_date) {
                    array_push($this->dates, $inspection_date);
                }
            }
            $month++;
            if ($month == 13) {
                $year++;
                $month = 1;
            }
        }
        return $this->dates;
    }


}