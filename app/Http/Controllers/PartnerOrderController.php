<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class PartnerOrderController extends Controller
{
    public function show($partner, Request $request)
    {
        $partner_order = $request->partner_order->load(['order', 'jobs.resource.profile']);
        $this->_getInfo($partner_order);
        removeRelationsFromModel($partner_order);
        removeSelectedFieldsFromModel($partner_order);
        return api_response($request, $partner_order, 200, ['order' => $partner_order]);
    }

    public function getOrders($partner, Request $request)
    {
        $partner = $request->partner;
        list($offset, $limit) = calculatePagination($request);
        $partner->load(['partner_orders' => function ($q) use ($offset, $limit, $request) {
            if ($request->status == 'ongoing') {
                $q->where('closed_at', null);
            } elseif ($request->status == 'history') {
                $q->where('closed_and_paid_at', '<>', null);
            }
            $q->orderBy('id', 'desc')->skip($offset)->take($limit);
        }]);
        $partner_orders = $partner->partner_orders->load(['jobs', 'order' => function ($q) {
            $q->with(['customer.profile', 'location']);
        }]);
        $partner_orders->each(function ($partner_order, $key) {
            $this->_getInfo($partner_order);
            removeRelationsFromModel($partner_order);
            removeSelectedFieldsFromModel($partner_order);
        });
        if (count($partner_orders) > 0) {
            return api_response($request, $partner_orders, 200, ['orders' => $partner_orders]);
        } else {
            return api_response($request, null, 404);
        }
    }

    private function _getInfo($partner_order)
    {
        $partner_order->calculate();
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->customer->profile->name;
        $partner_order['location'] = $partner_order->order->location->name;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['total_jobs'] = count($partner_order->jobs);
        $partner_order['order_status'] = $partner_order->status;
    }
}
