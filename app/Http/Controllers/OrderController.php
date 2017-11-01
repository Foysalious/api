<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
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

    public function getNotClosedOrderInfo($customer, Request $request)
    {
        $customer = $request->customer;
        $orders = $this->orderRepository->getOrderInfo($customer);
        $final_orders = [];
        foreach ($orders as $key => $order) {
            $order->calculate();
            if (in_array($order->status, ['Cancelled']) || ($order->status == 'Closed' && $order->due == 0)) {
                continue;
            }
            foreach ($order->partner_orders as $partner_order) {
                if ($partner_order->status == 'Cancelled' && count($partner_order->jobs) == 1) {
                    $job = $partner_order->jobs[0];
                    if ($job->partnerChangeLog != null) {
                        array_add($partner_order, 'show', false);
                        array_forget($partner_order, 'partner_collection');
                        array_forget($partner_order, 'sheba_collection');
                        array_forget($partner_order->partner, 'categories');
                        array_forget($job, 'partnerChangeLog');
                        continue;
                    }
                } else {
                    array_add($partner_order, 'show', true);
                }
                foreach ($partner_order->jobs as $job) {
                    if ($job->status == "Cancelled") {
                        if ($job->partnerChangeLog != null) {
                            array_add($job, 'show', false);
                            array_forget($partner_order, 'partner_collection');
                            array_forget($partner_order, 'sheba_collection');
                            array_forget($partner_order->partner, 'categories');
                            array_forget($job, 'partnerChangeLog');
                            continue;
                        } else {
                            array_add($job, 'show', true);
                        }
                    }
                    $job['code']=$job->fullCode();
                    array_add($job, 'customer_charge', $job->grossPrice);
                    array_add($job, 'material_price', $job->materialPrice);
                    array_forget($job, 'partner_order');
                }
                array_add($partner_order, 'total_amount', $partner_order->grossAmount);
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

    public function getClosedOrderInfo($customer, Request $request)
    {
        $customer = $request->customer;
        $orders = $this->orderRepository->getOrderInfo($customer);
        $final_orders = [];
        foreach ($orders as $key => $order) {
            $order->calculate();
            if (in_array($order->status, ['Open', 'Process', 'Cancelled']) || ($order->status == 'Closed' && $order->due != 0)) {
                continue;
            }
            foreach ($order->partner_orders as $partner_order) {
                array_add($partner_order, 'show', true);
                array_add($partner_order, 'total_amount', $partner_order->grossAmount);
                array_add($partner_order, 'paid_amount', $partner_order->paid);
                array_add($partner_order, 'due_amount', $partner_order->due);
                array_add($partner_order, 'rounding_cut_off', $partner_order->roundingCutOff);
                $job_partner_change = 0;
                foreach ($partner_order->jobs as $job) {
                    array_add($job, 'show', true);
                    if ($job->status == "Cancelled") {
                        if ($job->partnerChangeLog != null) {
                            $job['show'] = false;
                            $job_partner_change++;
                        }
                    }
                    array_add($job, 'customer_charge', $job->grossPrice);
                    array_add($job, 'material_price', $job->materialPrice);
                    array_forget($job, 'partnerChangeLog');
                }
                if (count($partner_order->jobs) == $job_partner_change) {
                    $partner_order['show'] = false;
                }
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

    public function getCancelledOrders($customer, Request $request)
    {
        $customer = $request->customer;
        $orders = $this->orderRepository->getOrderInfo($customer);
        $final_orders = [];
        foreach ($orders as $key => $order) {
            $order->calculate();
            if (in_array($order->status, ['Open', 'Process', 'Closed'])) {
                continue;
            }
            foreach ($order->partner_orders as $partner_order) {
                array_add($partner_order, 'show', true);
                array_add($partner_order, 'total_amount', $partner_order->grossAmount);
                array_add($partner_order, 'paid_amount', $partner_order->paid);
                array_add($partner_order, 'due_amount', $partner_order->due);
                array_add($partner_order, 'rounding_cut_off', $partner_order->roundingCutOff);
                $job_partner_change = 0;
                foreach ($partner_order->jobs as $job) {
                    array_add($job, 'show', true);
                    if ($job->status == "Cancelled") {
                        if ($job->partnerChangeLog != null) {
                            $job['show'] = false;
                            $job_partner_change++;
                        }
                    }
                    array_add($job, 'customer_charge', $job->grossPrice);
                    array_add($job, 'material_price', $job->materialPrice);
                    array_forget($job, 'partnerChangeLog');
                }
                if (count($partner_order->jobs) == $job_partner_change) {
                    $partner_order['show'] = false;
                }
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
}
