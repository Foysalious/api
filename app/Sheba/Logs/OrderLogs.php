<?php

namespace App\Sheba\Logs;

use App\Models\Order;

class OrderLogs
{
    private $order;
    private $deliveryChangeLogs;

    public function __construct($order)
    {
        $this->order = ($order instanceof Order) ? $order : Order::find($order);
    }

    public function all()
    {
        $this->deliveryChangeLogs = collect();
        $this->getDeliveryChanges($this->order->updateLogs);
        return [
            'delivery_info_change' => $this->deliveryChangeLogs
        ];
    }

    public function getDeliveryChanges($updateLogs)
    {
        foreach ($updateLogs as $updateLog) {
            $logs = $this->getDeliveryLogs($updateLog->old_data, $updateLog->new_data);
            foreach ($logs as $log) {
                $this->deliveryChangeLogs->push((object)[
                    'created_at' => $updateLog->created_at,
                    'created_by_name' => $updateLog->created_by_name,
                    'log' => $log
                ]);
            }
        }
    }

    private function getDeliveryLogs($old_data, $new_data)
    {
        $logs = [];
        if (trim($old_data->delivery_name) != trim($new_data->delivery_name)) {
            $logs[] = 'Delivery Name ' . $old_data->delivery_name . ' has been changed to ' . $new_data->delivery_name;
        }
        if (trim($old_data->delivery_mobile) != trim($new_data->delivery_mobile)) {
            $logs[] = 'Delivery Mobile ' . $old_data->delivery_mobile . ' has been changed to ' . $new_data->delivery_mobile;
        }
        if (trim($old_data->delivery_address) != trim($new_data->delivery_address)) {
            $logs[] = 'Delivery Address ' . $old_data->delivery_address . ' has been changed to ' . $new_data->delivery_address;
        }
        return $logs;
    }

}