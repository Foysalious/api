<?php

namespace App\Repositories;


class ServiceRepository {

    /**
     * @param $service
     * @param $location
     * @return mixed
     */
    public function partners($service, $location)
    {
        $service_partners = $this->partnerSelect($service);
        $final_partners=[];
        foreach ($service_partners as $key => $partner)
        {
            // If partner doesn't  provide service in this location then remove the partner from service_partner list
            if (!$this->partnerLocationExist($partner, $location))
            {
                array_forget($service_partners, $key);
                continue;
            }
            $prices = json_decode($partner->prices);
            array_forget($partner, 'pivot');

            if ($service->variable_type == 'Fixed' || $service->variable_type == 'Custom')
            {
                array_set($partner, 'prices', $prices);
            }
            elseif ($service->variable_type == 'Options')
            {
                $variables = json_decode($service->variables);
                // Get the first option of service
                $first_option = key($variables->prices);
                //check for first option exists in prices
                if (array_has($prices, $first_option))
                {
                    $price = reset($prices);// first value of array key
                    array_set($partner, 'prices', $price);
                }
                else
                {
                    //remove the partner from service_partner list
                    array_forget($service_partners, $key);
                    continue;
                }
            }
            array_add($partner, 'review', 100);
            array_add($partner, 'rating', 3.5);
            array_push($final_partners,$partner);
        }
        dd($final_partners);
        return $final_partners;

    }

    public function partnerWithSelectedOption($service, $option, $location)
    {
        //Get all partners of the service
        $service_partners = $this->partnerSelect($service);
        foreach ($service_partners as $key => $partner)
        {
            //If the person doesn't provide service in that location remove partner from service_partner list
            if (!$this->partnerLocationExist($partner, $location))
            {
                array_forget($service_partners, $key);
                continue;
            }
            $options = (array)json_decode($partner->prices);
            //check if the selected option exist in partenr option list otherwise remove the partner from partner list
            if (array_has($options, $option))
            {
                //price of the selected option
                $price = array_pull($options, $option);
                array_set($partner, 'prices', $price);
                array_add($partner, 'review', 100);
                array_add($partner, 'rating', 3.5);
                array_forget($partner, 'pivot');
            }
            else
            {
                //remove the partner from partner list
                array_forget($service_partners, $key);
            }

        }

        return $service_partners;
    }


    /**
     * Check if this partner provides service in that location or not
     * @param $partner
     * @param $location
     * @return bool
     */
    public function partnerLocationExist($partner, $location)
    {
        $location = $partner->locations()->where('location_id', $location)->first();
        if (empty($location))
        {
            return false;
        }
        return true;
    }

    /**
     * Select columns for partner
     * @param $service
     * @return mixed
     */
    public function partnerSelect($service)
    {
        return $service->partners()
            ->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'prices')
            ->get();
    }

}