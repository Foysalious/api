<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    public function __construct()
    {
    }

    public function getNotClosedOrderInfo($customer)
    {
        $customer = Customer::find($customer);
        $orders = $customer->orders()
            ->with(['partner_orders' => function ($query)
            {
                $query->select('id', 'partner_id', 'total_amount', 'order_id')
                    ->with(['partner' => function ($query)
                    {
                        $query->select('id', 'name');
                    }])
                    ->with(['jobs' => function ($query)
                    {
                        $query->select('id', 'service_id', 'service_cost', 'partner_order_id')
                            ->with(['service' => function ($query)
                            {
                                $query->select('id', 'name', 'thumb');
                            }]);
                    }]);
            }])->wherehas('jobs', function ($query)
            {
                $query->where('jobs.status', '<>', 'Closed');
            })->select('id', 'created_at')->get();
        foreach ($orders as $order)
        {
            array_add($order, 'total_amount', $order->partner_orders->sum('total_amount'));
        }
        return response()->json(['orders' => $orders]);

    }
}
