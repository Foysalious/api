<?php

namespace App\Repositories;

use Validator;

class PartnerOrderRepository
{

    public function validateShowRequest($request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string',
            'filter' => 'sometimes|required|string|in:ongoing,history'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function resolveStatus($filter)
    {
        if ($filter == 'ongoing') {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Served']);
        }
    }

    public function getStatusFromRequest($request)
    {
        if ($request->has('status')) {
            return explode(',', $request->status);
        } elseif ($request->has('filter')) {
            return $this->resolveStatus($request->filter);
        } else {
            return constants('JOB_STATUSES');
        }
    }

    public function getOrderInfo($partner_order)
    {
        $partner_order->calculate();
        $partner_order['due_amount'] = (double)$partner_order->due;
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->delivery_name;
        $partner_order['customer_mobile'] = $partner_order->order->delivery_mobile;
        $partner_order['address'] = $partner_order->order->delivery_address;
        $partner_order['location'] = $partner_order->order->location->name;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['total_jobs'] = count($partner_order->jobs);
        $partner_order['order_status'] = $partner_order->status;
        return $partner_order;
    }
}