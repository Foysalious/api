<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\CustomOrderDiscussion;
use App\Models\Service;
use Illuminate\Http\Request;


class CustomOrderController extends Controller
{
    public function askForQuotation(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $service = Service::find($request->service_id);
        $service_variables = json_decode($service->variables, 1);
        $custom_order_service_options = $request->options;
        $custom_order_options = [];
        foreach ($service_variables['options'] as $key => $option) {
            $question = $option['question'];
            if ($option['answers'] != '') {
                $answer = explode(',', $option['answers'])[$custom_order_service_options[$key]];
            } else {
                $answer = $custom_order_service_options[$key];
            }
            array_push($custom_order_options, [
                'question' => $question,
                'answer' => $answer
            ]);
        }
        $custom_order = new CustomOrder();
        $custom_order->customer_id = $customer->id;
        $custom_order->service_id = $service->id;
        $custom_order->service_variables = json_encode($custom_order_options);
        $custom_order->additional_info = $request->additional_info;
        $custom_order->sales_channel = isset($request->sales_channel) ? $request->sales_channel : 'Web';
        $custom_order->status = 'Open';

        if ($custom_order->save()) {
            return response()->json(['msg' => 'yeah!', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'failed', 'code' => 404]);
        }
    }

    public function getCustomOrders($customer)
    {
        $orders = CustomOrder::with(['service' => function ($q) {
            $q->select('id', 'name');
        }])->select('id', 'service_id', 'status', 'created_at')->where('customer_id', $customer)->get();
        if (count($orders) != 0) {
            return response()->json(['orders' => $orders, 'code' => 200]);
        } else {
            return response()->json(['code' => 404]);
        }
    }

    public function getCustomOrderQuotation($customer, $custom_order)
    {
        $orders = CustomOrder::with(['service' => function ($q) {
            $q->select('id', 'name');
        }])->select('id', 'service_id', 'status', 'created_at')->where('id', $custom_order)->get();
        if (count($orders) != 0) {
            return response()->json(['orders' => $orders, 'code' => 200]);
        } else {
            return response()->json(['code' => 404]);
        }
    }

    public function postCommentOnDiscussion($customer, $custom_order, Request $request)
    {
        $discussion = new CustomOrderDiscussion();
        $discussion->custom_order_id = $custom_order;
        $discussion->comment = $request->comment;
        $discussion->user_type = 'App\Models\CustomOrder';
        $discussion->created_by = $customer;
        if ($discussion->save()) {
            return response()->json(['code' => 200]);
        }
    }

    public function getCommentForDiscussion($customer, $custom_order)
    {
        $comments = CustomOrderDiscussion::where('custom_order_id', $custom_order)->select('comment')->get();
        return response()->json(['comments' => $comments]);
    }

}
