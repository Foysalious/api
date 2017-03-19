<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\CustomOrderDiscussion;
use App\Models\Service;
use App\Repositories\CustomOrderRepository;
use Illuminate\Http\Request;


class CustomOrderController extends Controller
{

    private $customOrderRepository;

    public function __construct()
    {
        $this->customOrderRepository = new CustomOrderRepository();
    }

    public function askForQuotation(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $service = Service::find($request->service_id);
        if ($this->customOrderRepository->save($customer, $service, $request)) {
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
        }])->with(['quotations' => function ($query) {
            $query->select('id', 'custom_order_id', 'proposal', 'partner_id', 'attachment', 'proposed_price')->where('is_sent', 1)
                ->with(['partner' => function ($q) {
                    $q->select('id', 'name');
                }]);
        }])->select('id', 'service_id', 'status', 'service_variables', 'created_at', 'additional_info')->where('id', $custom_order)->first();
        if (count($orders) != 0) {
            return response()->json(['order' => $orders, 'code' => 200]);
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
