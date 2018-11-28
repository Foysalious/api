<?php namespace Sheba\Analysis\PartnerSale\Calculators;

use Illuminate\Support\Collection;
use Sheba\Analysis\PartnerSale\PartnerSale;

class Basic extends PartnerSale
{
    protected function calculate()
    {
        $data = ['total_sales' => 5000.00, 'order_accepted' => 50, 'order_completed' => 50];

        if ($this->frequency == self::DAY_BASE) {
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['timeline'] = 'Thursday, Oct 31';
            $data['sheba_payable'] = 4832.56;
            $data['partner_collection'] = 483.56;
        }

        if ($this->frequency == self::WEEK_BASE) {
            $data['timeline'] = 'Oct 26 - Nov 1';
            $data['sheba_payable'] = 4832.56;
            $data['partner_collection'] = 483.56;
            $data['sales_stat_breakdown'] = [['value' => 'Sun', 'amount' => 455.58], ['value' => 'Mon', 'amount' => 4552], ['value' => 'Tue', 'amount' => 45005], ['value' => 'Wed', 'amount' => 4505,], ['value' => 'Thu', 'amount' => 455], ['value' => 'Fri', 'amount' => 4550], ['value' => 'Sat', 'amount' => 455]];
            $data['order_stat_breakdown'] = [['value' => 'Sun', 'amount' => 455], ['value' => 'Mon', 'amount' => 4552], ['value' => 'Tue', 'amount' => 45005], ['value' => 'Wed', 'amount' => 4505], ['value' => 'Thu', 'amount' => 455], ['value' => 'Fri', 'amount' => 4550], ['value' => 'Sat', 'amount' => 455]];
        }

        if ($this->frequency == self::MONTH_BASE) {
            $data['timeline'] = 'October';
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['sheba_payable'] = 4832.56;
            $data['partner_collection'] = 483.56;
            $data['sales_stat_breakdown'] = [['value' => 1, 'amount' => 11.22], ['value' => 2, 'amount' => 1121], ['value' => 3, 'amount' => 112.2], ['value' => 4, 'amount' => 11], ['value' => 5, 'amount' => 11]];
            $data['order_stat_breakdown'] = [['value' => 1, 'amount' => 10], ['value' => 2, 'amount' => 22], ['value' => 3, 'amount' => 11], ['value' => 4, 'amount' => 111], ['value' => 5, 'amount' => 101]];
        }

        if ($this->frequency == self::YEAR_BASE) {
            $data['timeline'] = 'Year 2018';
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['lifetime_sales'] = 483.56;
        }

        return $data;
    }
}