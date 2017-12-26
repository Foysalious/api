<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Job;
use App\Models\JobService;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerService;
use App\Models\Service;
use App\Repositories\JobServiceRepository;
use App\Repositories\OrderRepository;
use App\Sheba\Partner\PartnerList;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Redis;
use DB;

class OrderController extends Controller
{
    private $orderRepository;
    private $jobServiceRepository;
    private $job_statuses_show;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->jobServiceRepository = new JobServiceRepository();
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
                    $job['code'] = $job->fullCode();
                    array_add($job, 'customer_charge', $job->grossPrice);
                    array_add($job, 'material_price', $job->materialPrice);
                    array_forget($job, 'partner_order');
                }
                array_add($partner_order, 'total_amount', $partner_order->grossAmount);
                array_add($partner_order, 'paid_amount', $partner_order->paid);
                array_add($partner_order, 'due_amount', $partner_order->due);
                array_add($partner_order, 'total_price', (double)$partner_order->totalPrice);
                array_add($partner_order, 'rounding_cut_off', (double)$partner_order->roundingCutOff);
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
                array_add($partner_order, 'total_price', (double)$partner_order->totalPrice);
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

    public function store($customer, Request $request)
    {
        try {
            $service_details = collect(json_decode($request->services))->each(function ($item, $key) {
                $item->service = Service::find($item->service_id);
            });
            $partner_list = new PartnerList($request->location);
            $partner = $partner_list->getList($service_details, $request->date, $request->time, $request->partner)->first();
            if (count($partner) != 0) {
                $data = $request->only(['delivery_mobile', 'delivery_name', 'delivery_address', 'sales_channel']);
                $data['location_id'] = $request->location;
                $data['customer_id'] = $request->customer;
                $data['created_by'] = $created_by = $request->has('created_by') ? $request->created_by : 0;
                $data['created_by_name'] = $created_by_name = $request->has('created_by_name') ? $request->created_by_name : 'Customer';
                $order = new Order();
                try {
                    DB::transaction(function () use ($data, $request, $created_by_name, $created_by, $service_details, $partner, $order) {
                        $order = $order->create($data);
                        $order = Order::find($order->id);
                        $partner_order = PartnerOrder::create([
                            'created_by' => $created_by, 'created_by_name' => $created_by_name,
                            'order_id' => $order->id, 'partner_id' => $request->partner,
                            'payment_method' => $request->has('payment_method') ? $request->payment_method : 'cash-on-delivery'
                        ]);
                        $job = new Job();
                        $job->partner_order_id = $partner_order->id;
                        $job->schedule_date = $request->date;
                        $job->preferred_time = $request->time;
                        $job->save();
                        foreach ($partner->services as $service) {
                            $service_detail = $service_details->where('service_id', $service->id)->first();
                            $data = array(
                                'job_id' => $job->id,
                                'service_id' => $service_detail->service_id,
                                'quantity' => $service_detail->quantity,
                                'option' => $service_detail->option,
                                'created_by' => $created_by,
                                'created_by_name' => $created_by_name
                            );
                            $this->jobServiceRepository->save(PartnerService::find($service->pivot->id), $data);
                        }
                    });
                    return api_response($request, $order, 200, ['order_id' => $order->id]);
                } catch (QueryException $e) {
                    return api_response($request, null, 500);
                }
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
