<?php

namespace App\Repositories;

use App\Models\CustomerDeliveryAddress;
use App\Models\CustomOrder;
use App\Models\User;

class CustomOrderRepository
{

    /**
     * Save custom orders
     * @param $customer
     * @param $service
     * @param $request
     * @return bool
     */
    public function save($customer, $service, $request)
    {
        $custom_order = new CustomOrder();
        $custom_order->customer_id = $customer->id;
        $custom_order->service_id = $service->id;
        $custom_order->service_variables = '';
        if ($request->filled('options')) {
            $custom_order->service_variables = $this->getServiceVariables($request->options, $service);
        }
        $custom_order->additional_info = $request->additional_info;
        $custom_order->location_id = $request->location_id;
        $custom_order->schedule_date = $request->filled('schedule_date') ? $request->schedule_date : '';
        $custom_order->preferred_time = $request->filled('preferred_time') ? $request->preferred_time : '';
        if ($request->filled('address_id')) {
            $custom_order->delivery_address = (CustomerDeliveryAddress::find($request->address_id))->address;
        } elseif ($request->filled('address')) {
            $deliver_address = new CustomerDeliveryAddress();
            $deliver_address->address = $request->address;

            $deliver_address->customer_id = $customer->id;
            if ($request->filled('created_by')) {
                $deliver_address->created_by = $request->created_by;
                $deliver_address->created_by_name = User::find($request->created_by)->name;
            } else {
                $deliver_address->created_by_name = 'Customer';
            }

            $deliver_address->save();
            $custom_order->delivery_address = $request->address;
        }
        $custom_order->sales_channel = $request->filled('sales_channel') ? $request->sales_channel : 'Web';
        $custom_order->job_name = $request->filled('job_name') ? $request->job_name : '';
        $custom_order->crm_id = $request->filled('crm_id') ? $request->crm_id : null; //REMOVE
        $custom_order->created_by = $request->filled('created_by') ? $request->created_by : '';
        $custom_order->status = 'Open';
        if ($custom_order->save()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Process the service variables for custom order
     * @param $options
     * @param $service
     * @return string
     */
    private function getServiceVariables($options, $service)
    {
        $service_variables = json_decode($service->variables, 1);
        $custom_order_options = [];
        foreach ($service_variables['options'] as $key => $option) {
            $question = $option['question'];
            if ($option['answers'] != '') {
                $answer = explode(',', $option['answers'])[$options[$key]];
            } else {
                $answer = $options[$key];
            }
            array_push($custom_order_options, [
                'question' => $question,
                'answer' => $answer
            ]);
        }
        return json_encode($custom_order_options);
    }
}