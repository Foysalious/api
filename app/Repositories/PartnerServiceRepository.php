<?php

namespace App\Repositories;


use App\Models\PartnerService;
use App\Models\Service;

class PartnerServiceRepository
{
    public function getVariableOptionPriceOfService(Service $service, PartnerService $partner_service, Array $option)
    {
        if ($service->variable_type == 'Options') {
            $variables = [];
            $options = implode(',', $option);
            $unit_price = (double)(json_decode($partner_service->prices))->$options;
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $option = '[' . $options . ']';
            $variables = json_encode($variables);
        } else {
            $option = '[]';
            $variables = '[]';
            $unit_price = (double)$partner_service->prices;
        }
        return array($unit_price, $option, $variables);
    }


    public function getPriceOfThisOption($prices, $option)
    {
        $prices = json_decode($prices);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return (double)$price;
            }
        }
        return null;
    }

    public function hasThisOption($prices, $option)
    {
        $prices = json_decode($prices);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return true;
            }
        }
        return false;
    }
}