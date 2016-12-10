<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    private $orderRepository;
    private $job_statuses_show;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->job_statuses_show = config('constants.JOB_STATUSES_SHOW');
    }

    public function getNotClosedOrderInfo($customer)
    {
        $customer = Customer::find($customer);
        $orders = $this->orderRepository->getOrderInfo($customer, '<>', 'Closed');
        return response()->json(['orders' => $orders, 'code' => 200, 'msg' => 'successful']);
    }

    public function getClosedOrderInfo($customer)
    {
        $customer = Customer::find($customer);
        $orders = $customer->orders()
            ->with(['partner_orders' => function ($query)
            {
                $query->select('id', 'partner_id', 'total_amount', 'paid', 'due', 'order_id')
                    ->with(['partner' => function ($query)
                    {
                        $query->select('id', 'name');
                    }])
                    ->with(['jobs' => function ($query)
                    {
                        $query->select('id', 'service_id', 'service_cost', 'status', 'partner_order_id')
                            ->with(['service' => function ($query)
                            {
                                $query->select('id', 'name', 'thumb');
                            }]);
                    }]);
            }])->select('id', 'created_at')->get();

        $final_orders = [];
        foreach ($orders as $order)
        {
            $count = 0;
            foreach ($order->partner_orders as $partner_order)
            {
                foreach ($partner_order->jobs as $job)
                {
                    if ($job->status == "Open")
                    {
                        $count++;
                    }
                }
            }
            if ($count == 0)
            {
                array_push($final_orders, $order);
            }
        }
        return response()->json(['orders' => $final_orders, 'code' => 200, 'msg' => 'successful']);
    }
}
