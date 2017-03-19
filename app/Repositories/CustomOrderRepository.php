<?php

namespace App\Repositories;
use App\Models\CustomOrder;

class CustomOrderRepository
{

    public function save($customer, $service, $request)
    {
        $custom_order = new CustomOrder();
        $custom_order->customer_id = $customer->id;
        $custom_order->service_id = $service->id;
        $custom_order->service_variables = '';
        if (isset($request->options)) {
            $custom_order->service_variables = $this->getServiceVariables($request->options, $service);
        }
        $custom_order->additional_info = $request->additional_info;
        $custom_order->location_id = $request->location_id;
        $custom_order->sales_channel = isset($request->sales_channel) ? $request->sales_channel : 'Web';
        $custom_order->crm_id = isset($request->crm_id) ? $request->crm_id : '';
        $custom_order->status = 'Open';
        if ($custom_order->save()) {
            return true;
        } else {
            return false;
        }
    }

    private function getServiceVariables($options, $service)
    {
        $service_variables = json_decode($service->variables, 1);
        $custom_order_service_options = $options;
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
        return json_encode($custom_order_options);
    }
}