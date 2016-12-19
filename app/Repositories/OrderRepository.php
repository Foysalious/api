<?php

namespace App\Repositories;


class OrderRepository
{

    /**
     * @param $customer
     * @param $compareOperator
     * @param $status
     * @return mixed
     */
    public function getOrderInfo($customer, $compareOperator, $status)
    {
        return $customer->orders()
            ->with(['partner_orders' => function ($query) {
                $query->select('id', 'partner_id', 'discount', 'rounding_cut_off', 'sheba_collection', 'partner_collection', 'order_id')
                    ->with(['partner' => function ($query) {
                        $query->select('id', 'name');
                    }])
                    ->with(['jobs' => function ($query) {
                        $query->select('id', 'service_id', 'service_price', 'service_cost', 'discount', 'status', 'partner_order_id')
                            ->with(['service' => function ($query) {
                                $query->select('id', 'name', 'banner');
                            }]);
                    }]);
            }])->wherehas('jobs', function ($query) use ($status, $compareOperator) {
                $query->where('jobs.status', $compareOperator, $status);
            })->select('id', 'created_at')->get();

    }

}