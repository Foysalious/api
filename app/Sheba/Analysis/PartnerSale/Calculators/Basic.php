<?php namespace Sheba\Analysis\PartnerSale\Calculators;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Analysis\PartnerSale\PartnerSale;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\PartnerOrderRepository;

class Basic extends PartnerSale
{
    private $data;
    private $partnerOrders;

    /**
     * @return Collection|mixed
     */
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

            $data['sales_stat_breakdown'] = $this->getWeeklyStatFor($orders, 'sales');
            $data['order_stat_breakdown'] = $this->getWeeklyStatFor($orders, 'order_count');
        }

        if ($this->frequency == self::MONTH_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('F');
            $data['day'] = $this->timeFrame->start->format('Y-m-d');

            $data['sales_stat_breakdown'] = $this->getMonthlyStatFor($orders, 'sales');
            $data['order_stat_breakdown'] = $this->getMonthlyStatFor($orders, 'order_count');
        }

        if ($this->frequency == self::YEAR_BASE) {
            $lifetime_timeFrame = (new TimeFrame())->forLifeTime();
            $lifetime_closed_orders = $this->partnerOrders->getClosedOrdersBetween($lifetime_timeFrame, $this->partner);

            $data['timeline'] = 'Year ' . $this->timeFrame->start->year;
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['lifetime_sales'] = $lifetime_closed_orders->sum('totalPrice');
        }

        if (in_array($this->frequency, [self::DAY_BASE, self::WEEK_BASE, self::MONTH_BASE])) {
            $data['partner_collection'] = $orders->sum('partner_collection');

            list($payable_to, $payable_amount) = $this->payableTo($orders->sum('shebaReceivable'), $orders->sum('spPayable'));
            $data['payable_to'] = $payable_to;
            $data['payable_amount'] = (double)$payable_amount;
        }

        return $data;
    }

    /**
     * @param $sheba_receivable
     * @param $sp_payable
     * @return array
     */
    private function payableTo($sheba_receivable, $sp_payable)
    {
        if (!$sheba_receivable && !$sp_payable) return [null, 0];
        elseif ($sheba_receivable) return ['sheba', $sheba_receivable];
        elseif ($sp_payable) return ['partner', $sp_payable];
    }

    /**
     * @param $orders
     * @param string $for
     * @return array
     */
    private function getWeeklyStatFor($orders, $for = 'sales')
    {
        $this->initData(self::WEEK_BASE);
        $orders->each(function ($order) use ($for) {
            $this->data[$order->closed_at->format('D')]['amount'] += ($for == 'sales') ? $order->totalPrice : 1;
        });

        return collect($this->data)->values()->all();
    }

    /**
     * @param $orders
     * @param string $for
     * @return array
     */
    private function getMonthlyStatFor($orders, $for = 'sales')
    {
        $this->initData(self::MONTH_BASE, cal_days_in_month(CAL_GREGORIAN, $this->timeFrame->start->month, $this->timeFrame->start->year));
        $orders->each(function ($order) use ($for) {
            $this->data[intval($order->closed_at->format('d'))]['amount'] += ($for == 'sales') ? $order->totalPrice : 1;
        });

        return collect($this->data)->values()->all();
    }

    /**
     * @param $type
     * @param null $limit
     */
    private function initData($type, $limit = null)
    {
        if ($type == self::WEEK_BASE) {
            for($date = $this->timeFrame->start->copy(); $date->lte($this->timeFrame->end); $date->addDay()) {
                $this->data[$date->format('D')] = ['value' => $date->format('D'), 'date' => $date->format('d M'), 'amount' => 0];
            }
        } elseif ($type == self::MONTH_BASE) {
            for ($i = 1; $i <= $limit; $i++) {
                $this->data[$i] = ['value' => $i, 'amount' => 0];
            }
        }
    }
}