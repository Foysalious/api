<?php namespace Sheba\Logistics\LogisticsNatures;

class OneWayLogistic extends LogisticNature
{
    public function getLogisticRouteInfo()
    {
        $logistic_route_info = [];
        $partner    = $this->job->partnerOrder->partner;
        $customer   = $this->job->partnerOrder->order->customer->profile;
        $customer_delivery_address = $this->job->partnerOrder->order->deliveryAddress;

        if ($this->orderKey === "first_logistic_order_id") {
            $logistic_route_info = [
                "pickup_name"           => $partner->name,
                "pickup_image"          => $partner->logo,
                "pickup_mobile"         => $partner->mobile,
                "pickup_address"        => $partner->address,
                "pickup_address_geo"    => $partner->geo_informations,
                "delivery_name"         => $customer->name,
                "delivery_image"        => $customer->pro_pic,
                "delivery_mobile"       => $customer->mobile,
                "delivery_address"      => $customer_delivery_address->address,
                "delivery_address_geo"  => $customer_delivery_address->geo_informations
            ];
        }

        return $logistic_route_info;
    }
}