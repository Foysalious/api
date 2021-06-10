<?php

namespace Sheba\Reports\Accounting;

class ProfitLossReportData
{
    /**
     * @param $data
     * @return array
     */
    public function format_data($data) : array
    {
        $formatted_data = array();
        $formatted_data['sales'] = $data['operating_earning'];
        $formatted_data['total_sales'] = $this->getSum($data['operating_earning']);
        $formatted_data['cost_of_goods_sold'] = $data['cost_of_goods_sold'];
        $formatted_data['full_profit'] = $formatted_data['total_sales'] - $formatted_data['cost_of_goods_sold'];
        $formatted_data['operating_cost'] = $data['business_cost'];
        $formatted_data['total_operating_cost'] = $this->getSum($data['business_cost']);
        $formatted_data['non_operating_income'] = $data['non_operating_income'];
        $formatted_data['total_non_operating_income'] = $this->getSum($data['non_operating_income']);
        $formatted_data['non_operating_expense'] = $data['non_operating_expense'];
        $formatted_data['total_non_operating_expense'] = $this->getSum($data['non_operating_expense']);
        $formatted_data['net_profit'] = $formatted_data['full_profit'] - $formatted_data['total_operating_cost'] + $formatted_data['total_non_operating_income'] - $formatted_data['total_non_operating_expense'];

        return $formatted_data;
    }

    /**
     * @param $data
     * @return float
     */
    private function getSum($data): float
    {
        $total_earning = 0.0;
        foreach ($data as $earning)
            $total_earning += $earning['balance'];

        return $total_earning;
    }
}