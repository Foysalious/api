<?php namespace Sheba\CmDashboard;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\Voucher;

class PromoCounter
{
    private $voucher;

    public function forVoucher($voucher_id)
    {
        $this->voucher = $voucher_id;
    }

    /**
     * COUNT JOB STATUS, IF USER IS_CM THEN CM JOB COUNT OR ALL SHEBA JOB COUNT.
     *
     * @return array
     */
    public function get()
    {
        /**
         * @TODO EXECUTION TIME INCREASE, MOVE TO CACHING
         */
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 660);

        $vouchers = $this->voucher ? [$this->voucher] : $this->getValidVouchersId();
        $orders = $this->getOrdersWithStatusUsingVouchers($vouchers);
        $closed_orders = $orders->where('status', 'Closed');

        $data['added'] = Promotion::select('DISTINCT customer_id')->whereIn('voucher_id', $vouchers)->count();
        $data['applied_order'] = $orders->count();
        $data['applied_customer'] = $orders->pluck('customer_id')->unique()->count();
        $data['used_order'] = $closed_orders->count();
        $data['used_customer'] = $closed_orders->pluck('customer_id')->unique()->count();

        return $data;
    }

    private function getValidVouchersId()
    {
        return Voucher::valid()->isVoucher()->pluck('id');
    }

    private function getOrdersWithStatusUsingVouchers($vouchers)
    {
        return Order::select('id', 'customer_id')->with(['partnerOrders' => function($q) {
            $q->select('id', 'order_id')->with(['jobs' => function ($q2) {
                $q2->select('id', 'partner_order_id', 'status');
            }]);
        }])->whereIn('voucher_id', $vouchers)->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'customer_id' => $order->customer_id,
                'status' => $order->getStatus()
            ];
        });
    }
}
