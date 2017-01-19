<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Job;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Redis;

class OrderController extends Controller
{
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
        //2nd & 3rd parameters are redundant
        $orders = $this->orderRepository->getOrderInfo($customer, '<>', 'Closed');
        $final_orders = [];
        foreach ($orders as $key => $order) {
            $order->calculate();
//            if ($order->status == 'Closed') {
////                unset($orders[$key]);
//                continue;
//            }
            if (in_array($order->status, ['Closed', 'Cancelled'])) {
                continue;
            }
            foreach ($order->partner_orders as $partner_order) {
                foreach ($partner_order->jobs as $job) {
                    array_add($job, 'customer_charge', $job->grossPrice);
                    array_add($job, 'material_price', $job->materialPrice);
                    array_forget($job, 'partner_order');
                }
                array_add($partner_order, 'total_amount', $partner_order->totalPrice);
                array_add($partner_order, 'paid_amount', $partner_order->paid);
                array_add($partner_order, 'due_amount', $partner_order->due);
                array_forget($partner_order, 'partner_collection');
                array_forget($partner_order, 'sheba_collection');
                array_forget($partner_order->partner, 'categories');
            }
            array_add($order, 'total_cost', $order->totalPrice);
            array_add($order, 'due_amount', $order->due);
            array_add($order, 'order_code', $order->code());
            array_push($final_orders, $order);
        }
        return response()->json(['orders' => $final_orders, 'code' => 200, 'msg' => 'successful']);
    }

    public function getClosedOrderInfo($customer)
    {
        $customer = Customer::find($customer);
        $orders = $this->orderRepository->getOrderInfo($customer, '=', 'Served');
        $final_orders = [];
        foreach ($orders as $key => $order) {
            $order->calculate();
            if (in_array($order->status, ['Open', 'Process'])) {
//                unset($orders[$key]);
                continue;
            }
            foreach ($order->partner_orders as $partner_order) {
                foreach ($partner_order->jobs as $job) {
                    array_add($job, 'customer_charge', $job->grossPrice);
                    array_add($job, 'material_price', $job->materialPrice);
                    array_forget($job, 'partner_order');
                }
                array_add($partner_order, 'total_amount', $partner_order->totalPrice);
                array_add($partner_order, 'paid_amount', $partner_order->paid);
                array_add($partner_order, 'due_amount', $partner_order->due);
                array_add($partner_order, 'rounding_cut_off', $partner_order->roundingCutOff);
                array_forget($partner_order, 'partner_collection');
                array_forget($partner_order, 'sheba_collection');
                array_forget($partner_order->partner, 'categories');
            }
            array_add($order, 'total_cost', $order->totalPrice);
            array_add($order, 'due_amount', $order->due);
            array_add($order, 'order_code', $order->code());
            array_push($final_orders, $order);
        }
        return response()->json(['orders' => $final_orders, 'code' => 200, 'msg' => 'successful']);
    }

    public function checkOrderValidity(Request $request)
    {
        $key = Redis::get($request->input('s_token'));
        if ($key != null) {
            Redis::del($request->input('s_token'));
            return response()->json(['msg' => 'successful', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'not found', 'code' => 404]);
        }
    }
//    public function getClosedOrderInfo($customer)
//    {
//        $customer = Customer::find($customer);
//        $orders = $customer->orders()
//            ->with(['partner_orders' => function ($query) {
//                $query->select('id', 'partner_id', 'total_amount', 'paid', 'due', 'order_id')
//                    ->with(['partner' => function ($query) {
//                        $query->select('id', 'name');
//                    }])
//                    ->with(['jobs' => function ($query) {
//                        $query->select('id', 'job_code', 'service_id', 'service_cost', 'total_cost', 'status', 'partner_order_id')
//                            ->with(['service' => function ($query) {
//                                $query->select('id', 'name', 'thumb');
//                            }]);
//                    }]);
//            }])->select('id', 'created_at')->get();
//
//        $final_orders = [];
//        foreach ($orders as $order) {
//            $count = 0;
//            foreach ($order->partner_orders as $partner_order) {
//                foreach ($partner_order->jobs as $job) {
//                    if ($job->status == "Open") {
//                        $count++;
//                    }
//                }
//            }
//            if ($count == 0) {
//                array_push($final_orders, $order);
//            }
//        }
//        return response()->json(['orders' => $final_orders, 'code' => 200, 'msg' => 'successful']);
//    }
}
