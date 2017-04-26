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
    public function getOrderInfo($customer)
    {
        return $customer->orders()
            ->with(['partner_orders' => function ($query) {
                $query->select('id', 'partner_id', 'discount', 'sheba_collection', 'invoice', 'partner_collection', 'order_id')
                    ->with(['partner' => function ($query) {
                        $query->select('id', 'name')->with(['categories' => function ($query) {
                            $query->select('category_id');
                        }]);
                    }])
                    ->with(['jobs' => function ($query) {
                        $query->select('id', 'service_id', 'service_unit_price', 'service_quantity', 'discount', 'status', 'partner_order_id')
                            ->with(['service' => function ($query) {
                                $query->select('id', 'name', 'category_id')->with(['category' => function ($query) {
                                    $query->select('categories.id');
                                }]);
                            }]);
                    }]);
            }])->with(['location' => function ($query) {
                $query->select('id', 'name');
            }])->select('id', 'delivery_mobile', 'delivery_name', 'delivery_address', 'sales_channel', 'location_id', 'created_at')->orderBy('id', 'desc')->get();

//        ->wherehas('jobs', function ($query) use ($status, $compareOperator) {
//        $query->where('jobs.status', $compareOperator, $status);
//    })

    }

}