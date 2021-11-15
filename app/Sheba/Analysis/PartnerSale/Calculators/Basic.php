<?php namespace Sheba\Analysis\PartnerSale\Calculators;

use App\Models\PosOrder;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use App\Sheba\PosOrderService\PosOrderServerClient;
use App\Sheba\UserMigration\Modules;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Analysis\PartnerSale\PartnerSale;
use Sheba\Helpers\TimeFrame;
use Sheba\Payment\Statuses;
use Sheba\Pos\Order\OrderPaymentStatuses;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Repositories\PartnerOrderRepository;

class Basic extends PartnerSale
{
    private $data;
    private $partnerOrders;
    private $posOrders;

    /**
     * @throws PosOrderServiceServerError
     */
    private function getNewPosOrderInformation()
    {
        /** @var PosOrderServerClient $client */
        $client = app(PosOrderServerClient::class);
        $sales_details = $client->get('api/v1/partners/' . $this->partner->id . '/sales?from=2021-11-15&to=2021-11-16');
        $orders = $this->partnerOrders->getClosedOrdersBetween($this->timeFrame, $this->partner);
        $accepted_orders = $this->partnerOrders->getAcceptedOrdersBetween($this->timeFrame, $this->partner);
        $pos_orders = $this->posOrders->getCreatedOrdersBetween($this->timeFrame, $this->partner);
        $pos_payment_status_wise_count = [OrderPaymentStatuses::PAID => 0, OrderPaymentStatuses::DUE => 0];
        $pos_orders->map(function ($pos_order) use (&$pos_payment_status_wise_count) {
            /** @var PosOrder $pos_order */
            $pos_order->sale = $pos_order->getNetBill();
            $pos_order->paidAmount = $pos_order->getPaid();
            $pos_order->dueAmount = $pos_order->getDue();
            $pos_payment_status_wise_count[$pos_order->getPaymentStatus()]++;
        });

        $pos_sales_count = $pos_orders->count();
        $pos_sales = $pos_orders->sum('sale');
        $pos_paid = $pos_orders->sum('paidAmount');
        $pos_due = $pos_orders->sum('dueAmount');

        $data['sheba_sales'] = $orders->sum('gmv');
        $data['pos_sales'] = $pos_sales;
        $data['total_sales'] = $data['sheba_sales'] + $data['pos_sales'];

        $data['order_accepted'] = $accepted_orders ? $accepted_orders->count : 0;
        $data['order_completed'] = $orders->count();
        $data['pos_order_created'] = $pos_orders->count();
        $data['pos'] = [
            'sales' => ['count' => $pos_sales_count, 'amount' => $pos_sales],
            'paid' => ['count' => $pos_payment_status_wise_count[OrderPaymentStatuses::PAID], 'amount' => $pos_paid],
            'due' => ['count' => $pos_payment_status_wise_count[OrderPaymentStatuses::DUE], 'amount' => $pos_due]
        ];

        $this->formatTimeline($data);

        if ($this->frequency == self::DAY_BASE) {
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['timeline'] = $this->timeFrame->start->format('l, M d');
        }

        if ($this->frequency == self::WEEK_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('M d') . ' - ' . $this->timeFrame->end->format('M d');

            $data['sales_stat_breakdown'] = $this->getWeeklyStatFor($orders, 'sales', $pos_orders);
            $data['order_stat_breakdown'] = $this->getWeeklyStatFor($orders, 'order_count');
        }

        if ($this->frequency == self::MONTH_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('F');
            $data['day'] = $this->timeFrame->start->format('Y-m-d');

            $data['sales_stat_breakdown'] = $this->getMonthlyStatFor($orders, 'sales', $pos_orders);
            $data['order_stat_breakdown'] = $this->getMonthlyStatFor($orders, 'order_count');
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
     * @return Collection|mixed
     */
    protected function calculate()
    {
        $this->partnerOrders = new PartnerOrderRepository();
        $orders = $this->partnerOrders->getClosedOrdersBetween($this->timeFrame, $this->partner);
        $accepted_orders = $this->partnerOrders->getAcceptedOrdersBetween($this->timeFrame, $this->partner);

        $this->posOrders = new PosOrderRepository();
        if (!$this->partner->isMigrated(Modules::POS)) {
            $this->getNewPosOrderInformation();
        }
        $pos_orders = $this->posOrders->getCreatedOrdersBetween($this->timeFrame, $this->partner);
        $pos_payment_status_wise_count = [OrderPaymentStatuses::PAID => 0, OrderPaymentStatuses::DUE => 0];
        $pos_orders->map(function ($pos_order) use (&$pos_payment_status_wise_count) {
            /** @var PosOrder $pos_order */
            $pos_order->sale = $pos_order->getNetBill();
            $pos_order->paidAmount = $pos_order->getPaid();
            $pos_order->dueAmount = $pos_order->getDue();
            $pos_payment_status_wise_count[$pos_order->getPaymentStatus()]++;
        });

        $pos_sales_count = $pos_orders->count();
        $pos_sales = $pos_orders->sum('sale');
        $pos_paid = $pos_orders->sum('paidAmount');
        $pos_due = $pos_orders->sum('dueAmount');

        $data['sheba_sales'] = $orders->sum('gmv');
        $data['pos_sales'] = $pos_sales;
        $data['total_sales'] = $data['sheba_sales'] + $data['pos_sales'];

        $data['order_accepted'] = $accepted_orders ? $accepted_orders->count : 0;
        $data['order_completed'] = $orders->count();
        $data['pos_order_created'] = $pos_orders->count();
        $data['pos'] = [
            'sales' => ['count' => $pos_sales_count, 'amount' => $pos_sales],
            'paid' => ['count' => $pos_payment_status_wise_count[OrderPaymentStatuses::PAID], 'amount' => $pos_paid],
            'due' => ['count' => $pos_payment_status_wise_count[OrderPaymentStatuses::DUE], 'amount' => $pos_due]
        ];

        $this->formatTimeline($data);

        if ($this->frequency == self::DAY_BASE) {
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['timeline'] = $this->timeFrame->start->format('l, M d');
        }

        if ($this->frequency == self::WEEK_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('M d') . ' - ' . $this->timeFrame->end->format('M d');

            $data['sales_stat_breakdown'] = $this->getWeeklyStatFor($orders, 'sales', $pos_orders);
            $data['order_stat_breakdown'] = $this->getWeeklyStatFor($orders, 'order_count');
        }

        if ($this->frequency == self::MONTH_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('F');
            $data['day'] = $this->timeFrame->start->format('Y-m-d');

            $data['sales_stat_breakdown'] = $this->getMonthlyStatFor($orders, 'sales', $pos_orders);
            $data['order_stat_breakdown'] = $this->getMonthlyStatFor($orders, 'order_count');
        }

//        if ($this->frequency == self::YEAR_BASE) {
//            $lifetime_timeFrame = (new TimeFrame())->forLifeTime();
//            $lifetime_closed_orders = $this->partnerOrders->getClosedOrdersBetween($lifetime_timeFrame, $this->partner);
//
//            $data['timeline'] = 'Year ' . $this->timeFrame->start->year;
//            $data['day'] = $this->timeFrame->start->format('Y-m-d');
//            $data['lifetime_sales'] = $lifetime_closed_orders->sum('totalPrice');
//        }

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
        if (!$sheba_receivable && !$sp_payable) return [null, 0]; elseif ($sheba_receivable) return ['sheba', $sheba_receivable];
        elseif ($sp_payable) return ['partner', $sp_payable];
    }

    /**
     * @param $orders
     * @param string $for
     * @param null $pos_orders
     * @return array
     */
    private function getWeeklyStatFor($orders, $for = 'sales', $pos_orders = null)
    {
        $this->initData(self::WEEK_BASE);
        $this->getOrdersBreakdownData($orders, $for, self::WEEK_BASE);
        if ($pos_orders) $this->getPosOrdersBreakdownData($for, $pos_orders, self::WEEK_BASE);

        return collect($this->data)->values()->all();
    }

    /**
     * @param $orders
     * @param string $for
     * @param null $pos_orders
     * @return array
     */
    private function getMonthlyStatFor($orders, $for = 'sales', $pos_orders = null)
    {
        $this->initData(self::MONTH_BASE, cal_days_in_month(CAL_GREGORIAN, $this->timeFrame->start->month, $this->timeFrame->start->year));
        $this->getOrdersBreakdownData($orders, $for, self::MONTH_BASE);
        if ($pos_orders) $this->getPosOrdersBreakdownData($for, $pos_orders, self::MONTH_BASE);

        return collect($this->data)->values()->all();
    }

    /**
     * @param $orders
     * @param $for
     * @param $timeline_base
     */
    private function getOrdersBreakdownData($orders, $for, $timeline_base)
    {
        $is_calculating_for_month = ($timeline_base == self::MONTH_BASE);
        $orders->each(function ($order) use ($for, $is_calculating_for_month) {
            $order_created_at_formatter = $is_calculating_for_month ? intval($order->closed_at->format('d')) : $order->closed_at->format('D');
            $this->data[$order_created_at_formatter]['amount'] += ($for == 'sales') ? $order->totalPrice : 1;
        });
    }

    /**
     * @param $for
     * @param $pos_orders
     * @param $timeline_base
     */
    private function getPosOrdersBreakdownData($for, $pos_orders, $timeline_base)
    {
        $is_calculating_for_month = ($timeline_base == self::MONTH_BASE);
        $pos_orders->each(function ($pos_order) use ($for, $is_calculating_for_month) {
            $pos_order_created_at_formatter = $is_calculating_for_month ? intval($pos_order->created_at->format('d')) : $pos_order->created_at->format('D');
            $this->data[$pos_order_created_at_formatter]['amount'] += ($for == 'sales') ? $pos_order->getNetBill() : 1;
        });
    }

    /**
     * @param $type
     * @param null $limit
     */
    private function initData($type, $limit = null)
    {
        if ($type == self::WEEK_BASE) {
            for ($date = $this->timeFrame->start->copy(); $date->lte($this->timeFrame->end); $date->addDay()) {
                $this->data[$date->format('D')] = ['value' => $date->format('D'), 'date' => $date->format('d M'), 'amount' => 0];
            }
        } elseif ($type == self::MONTH_BASE) {
            for ($i = 1; $i <= $limit; $i++) {
                $this->data[$i] = ['value' => $i, 'amount' => 0];
            }
        }
    }

    /**
     * @param $data
     */
    private function formatTimeline(&$data)
    {
        if ($this->frequency == self::DAY_BASE) {
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
            $data['timeline'] = $this->timeFrame->start->format('l, M d');
        } elseif ($this->frequency == self::WEEK_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('M d') . ' - ' . $this->timeFrame->end->format('M d');
        } elseif ($this->frequency == self::MONTH_BASE) {
            $data['timeline'] = $this->timeFrame->start->format('F');
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
        } elseif ($this->frequency == self::YEAR_BASE) {
            $data['timeline'] = 'Year ' . $this->timeFrame->start->year;
            $data['day'] = $this->timeFrame->start->format('Y-m-d');
        }
        $data['time_frame'] = [
            'start' => $this->timeFrame->start->toDateString(),
            'end' => $this->timeFrame->end->toDateString()
        ];
    }
}
