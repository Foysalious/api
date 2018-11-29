<?php namespace Sheba\Analysis\PartnerSale\Calculators;

use Illuminate\Support\Collection;
use Sheba\Analysis\PartnerSale\PartnerSale;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\PartnerOrderRepository;

class Basic extends PartnerSale
{
    private $partnerOrders;

    protected function calculate()
    {
        $this->partnerOrders = new PartnerOrderRepository();
        $orders = $this->partnerOrders->getClosedOrdersBetween($this->timeFrame, $this->partner);
        $accepted_orders = $this->partnerOrders->getAcceptedOrdersBetween($this->timeFrame, $this->partner);

        $data['total_sales'] = $orders->sum('totalPrice');
        $data['order_accepted'] = $accepted_orders ? $accepted_orders->count : 0;
        $data['order_completed'] = $orders->count();

        if ($this->frequency == self::DAY_BASE) {
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['timeline'] = $this->timeFrame->start->format('l, M d');
        }

        if ($this->frequency == self::WEEK_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('M d') . ' - ' . $this->timeFrame->end->format('M d');
            // $data['sales_stat_breakdown'] = $this->getSalesStatBreakdown($this->frequency, $orders);
            // $data['order_stat_breakdown'] = $this->getOrdersStatBreakdown($this->frequency, $orders);
            $data['sales_stat_breakdown'] = [['value' => 'Sun', 'amount' => 455.58], ['value' => 'Mon', 'amount' => 4552], ['value' => 'Tue', 'amount' => 45005], ['value' => 'Wed', 'amount' => 4505,], ['value' => 'Thu', 'amount' => 455], ['value' => 'Fri', 'amount' => 4550], ['value' => 'Sat', 'amount' => 455]];
            $data['order_stat_breakdown'] = [['value' => 'Sun', 'amount' => 455], ['value' => 'Mon', 'amount' => 4552], ['value' => 'Tue', 'amount' => 45005], ['value' => 'Wed', 'amount' => 4505], ['value' => 'Thu', 'amount' => 455], ['value' => 'Fri', 'amount' => 4550], ['value' => 'Sat', 'amount' => 455]];
        }

        if ($this->frequency == self::MONTH_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('F');
            $data['day'] = $this->timeFrame->start->format('Y-m-d');

            $data['sales_stat_breakdown'] = [['value' => 1, 'amount' => 11.22], ['value' => 2, 'amount' => 1121], ['value' => 3, 'amount' => 112.2], ['value' => 4, 'amount' => 11], ['value' => 5, 'amount' => 11]];
            $data['order_stat_breakdown'] = [['value' => 1, 'amount' => 10], ['value' => 2, 'amount' => 22], ['value' => 3, 'amount' => 11], ['value' => 4, 'amount' => 111], ['value' => 5, 'amount' => 101]];
        }

        if ($this->frequency == self::YEAR_BASE) {
            $lifetime_timeFrame = (new TimeFrame())->forLifeTime();
            $all_closed_orders = $this->partnerOrders->getClosedOrdersBetween($lifetime_timeFrame, $this->partner);

            $data['timeline'] = 'Year ' . $this->timeFrame->start->year;
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['lifetime_sales'] = $all_closed_orders->sum('totalPrice');
        }

        if (in_array($this->frequency, [self::DAY_BASE, self::WEEK_BASE, self::MONTH_BASE])) {
            $data['partner_collection'] = $orders->sum('partner_collection');

            list($payable_to, $payable_amount) = $this->payableTo($orders->sum('shebaReceivable'), $orders->sum('spPayable'));
            $data['payable_to'] = $payable_to;
            $data['payable_amount'] = $payable_amount;
        }

        return $data;
    }

    private function payableTo($sheba_receivable, $sp_payable)
    {
        if (!$sheba_receivable && !$sp_payable) return [null, 0];
        elseif ($sheba_receivable > $sp_payable) return ['sheba', $sheba_receivable];
        elseif ($sp_payable > $sheba_receivable) return ['partner', $sp_payable];
    }

    private function getSalesStatBreakdown($frequency, $orders)
    {
        dd($frequency, $orders);
    }

    private function getOrdersStatBreakdown($frequency, $orders)
    {
        dd($frequency, $orders);
    }
}