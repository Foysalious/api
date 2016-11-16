<?php

namespace App\Repositories;


class ServiceRepository {

    public function partners($service)
    {
        $service_partners = $service->partners()
            ->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'prices')
            ->get();
        foreach ($service_partners as $partner)
        {
            array_forget($partner, 'pivot');
            if ($service->variable_type == 'Fixed' || $service->variable_type == 'Custom')
            {
                //convert string price to number
                $prices = json_decode($partner->prices);
                array_set($partner, 'prices', $prices);
                array_add($partner, 'review', 100);
                array_add($partner, 'rating', 3.5);
            }
            elseif ($service->variable_type == 'Options')
            {
                $variables = json_decode($service->variables);
                // Get the first option of service
                $first_option = key($variables->prices);
                $prices = json_decode($partner->prices);
                //check for first option exists in prices
                if (array_has($prices, $first_option))
                {
                    $price = reset($prices);
                    array_add($partner,'first_option',$first_option);
                    array_add($partner,'price_option',$prices);
                    array_set($partner, 'prices', $price);
                    array_add($partner, 'review', 100);
                    array_add($partner, 'rating', 3.5);
                }
                else
                    //remove the partner from partner list
                    unset($partner);
            }
        }

        return $service_partners;

    }

}