<?php


namespace Sheba\Subscription\Types;


use Carbon\Carbon;

class WeeklySubscriptionType extends SubscriptionType
{

    /**
     * @return Carbon[]
     */
    public function getDates()
    {
        $month = $this->currentMonth;
        $year = $this->currentYear;
        while ($this->toDate > Carbon::createFromDate($year, $month, $this->values->first())) {
            foreach ($this->values as $date) {
                $inspection_date = Carbon::createFromDate($year, $month, $date);
                while ((int)$inspection_date->format('m') != $month && (int)$inspection_date->format('d') != $date) {
                    $inspection_date = Carbon::createFromDate($year, $month, $date - 1);
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