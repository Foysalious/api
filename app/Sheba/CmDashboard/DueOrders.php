<?php namespace Sheba\CmDashboard;

use App\Models\Order;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DueOrders
{
    private $user;
    private $data;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->data = collect([]);
    }

    /**
     * GET INFORMATION ABOUT DUE ORDER BASED ON TIME FRAME
     *
     * @param $from
     * @param $to
     * @return \Illuminate\Support\Collection
     */
    public function get($from, $to)
    {
        $orders = $this->makeQuery()->whereBetween('partner_orders.closed_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->get()->all();
        $orders_id  = array_unique(array_pluck($orders, 'id'));
        $orders     = Order::findMany($orders_id);

        $orders->each(function ($order) {
            $order = Order::with('customer')->find($order->id);
            $this->data->push([
                'order_id'      => $order->id,
                'order_code'    => $order->code(),
                'customer_name' => !empty($order->customer) ? $order->customer->name : "N/A",
                'created_at'    => $order->created_at->format('d-M-y h:i:A')
            ]);
        });

        return $this->data;
    }

    /**
     * MAKING DUE ORDER QUERY
     *
     * @return mixed
     */
    private function makeQuery()
    {
        //$due_orders_query = Job::with('partnerOrder.order.customer', 'crm', 'partnerOrder.partner', 'partnerOrder.jobs.usedMaterials');
        $due_orders_query = DB::table('jobs')
            ->select('orders.id', 'orders.created_at')
            ->join('partner_orders', 'jobs.partner_order_id', '=', 'partner_orders.id')
            ->join('orders', 'orders.id', '=', 'partner_orders.order_id')
            ->whereNotNull('partner_orders.closed_at')
            ->whereNull('partner_orders.closed_and_paid_at');

        if ($this->user->is_cm) {
            //$due_orders_query->forCM($this->user->id);
            $due_orders_query->where('jobs.crm_id', $this->user->id);
        }

        return $due_orders_query;
    }
}