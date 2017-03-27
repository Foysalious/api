<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Customer;
use App\Models\CustomOrder;
use App\Models\CustomOrderDiscussion;
use App\Models\Service;
use App\Repositories\CustomOrderRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;


class CustomOrderController extends Controller
{

    private $customOrderRepository;

    public function __construct()
    {
        $this->customOrderRepository = new CustomOrderRepository();
    }

    /**
     * @param Request $request
     * @param $customer
     * @return \Illuminate\Http\JsonResponse
     */
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
        $discussion->user_type = 'App\Models\Customer';
        $discussion->created_by = $customer;
        if ($discussion->save()) {
            $customer = Customer::find($customer);
            return response()->json(['code' => 200, 'name' => $customer->name,
                'updated_at' => $discussion->updated_at->format('Y-m-d h:i A')]);
        } else {
            return response()->json(['code' => 500]);
        }
    }

    public function getCommentForDiscussion($customer, $custom_order)
    {
        $comments = CustomOrderDiscussion::with('writer')
            ->where('custom_order_id', $custom_order)
            ->select('id', 'user_type', 'created_by', 'comment', 'updated_at')->orderBy('created_at')->get();

        if (count($comments) > 0) {
            $final_comments = [];
            foreach ($comments as $comment) {
                $class_name = explode("\\", get_class($comment->writer));
                if ($class_name[count($class_name) - 1] == 'User') {
                    array_add($comment, 'name', $comment->writer->name);
                    array_add($comment, 'pro_pic', $comment->writer->profile_pic);
                } else {
                    array_add($comment, 'name', $comment->writer->profile->name);
                    array_add($comment, 'pro_pic', $comment->writer->profile->pro_pic);
                }
                array_forget($comment, 'writer');
                $time = $comment->updated_at;
                array_forget($comment, 'updated_at');
                array_add($comment, 'time', $time->format('Y-m-d h:i A'));
                array_push($final_comments, $comment);
            }
            return response()->json(['comments' => $final_comments, 'code' => 200]);
        } else {
            return response()->json(['code' => 404]);
        }
    }

}
