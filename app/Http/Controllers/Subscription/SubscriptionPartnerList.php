<?php

namespace App\Http\Controllers\Subscription;


use App\Sheba\Checkout\PartnerList;
use Sheba\Checkout\SubscriptionPrice;

class SubscriptionPartnerList extends PartnerList
{

    protected function calculateServicePricingAndBreakdownOfPartner($partner)
    {
        $total_service_price = [
            'discount' => 0,
            'discounted_price' => 0,
            'original_price' => 0,
            'is_min_price_applied' => 0,
            'total_quantity' => 0
        ];
        $services = [];
        $category_pivot = $partner->categories->first()->pivot;
        foreach ($this->partnerListRequest->selectedServices as $selected_service) {
            $service = $partner->services->where('id', $selected_service->id)->first();
            $discount = new SubscriptionPrice();
            $discount->setType($this->partnerListRequest->subscriptionType)->setServiceObj($selected_service)->setServicePivot($service->pivot)->initialize();
            $service = [];
            $service['discount'] = $discount->discount;
            $service['cap'] = $discount->cap;
            $service['amount'] = $discount->amount;
            $service['is_percentage'] = $discount->isDiscountPercentage;
            $service['discounted_price'] = $discount->discounted_price;
            $service['original_price'] = $discount->original_price;
            $service['min_price'] = $discount->min_price;
            $service['unit_price'] = $discount->unit_price;
            $service['sheba_contribution'] = $discount->sheba_contribution;
            $service['partner_contribution'] = $discount->partner_contribution;
            $service['is_min_price_applied'] = $discount->original_price == $discount->min_price ? 1 : 0;
            if ($discount->original_price == $discount->min_price) {
                $total_service_price['is_min_price_applied'] = 1;
            }
            $total_service_price['discount'] += $service['discount'];
            $total_service_price['discounted_price'] += $service['discounted_price'];
            $total_service_price['original_price'] += $service['original_price'];
            $service['id'] = $selected_service->id;
            $service['name'] = $selected_service->serviceModel->name;
            $service['option'] = $selected_service->option;
            $service['quantity'] = $selected_service->quantity;
            $service['unit'] = $selected_service->serviceModel->unit;
            list($option, $variables) = $this->getVariableOptionOfService($selected_service->serviceModel, $selected_service->option);
            $service['questions'] = json_decode($variables);
            $total_service_price['total_quantity'] += $service['quantity'];
            array_push($services, $service);
        }
        array_add($partner, 'breakdown', $services);
        $delivery_charge = (double)$category_pivot->delivery_charge;
        $total_service_price['discounted_price'] += $delivery_charge;
        $total_service_price['original_price'] += $delivery_charge;
        $total_service_price['delivery_charge'] = $delivery_charge;
        $total_service_price['total_quantity'] *= $this->partnerListRequest->getSubscriptionQuantity();
        $total_service_price['discounted_price'] *= $total_service_price['total_quantity'];
        $total_service_price['original_price'] *= $total_service_price['total_quantity'];
        $total_service_price['delivery_charge'] *= $total_service_price['total_quantity'];
        $total_service_price['discount'] *= $total_service_price['total_quantity'];
        return $total_service_price;
    }


}