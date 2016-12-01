<?php

namespace App\Repositories;


class ServiceRepository {

    /**
     * @param $service
     * @param $location
     * @return mixed
     */
    public function partners($service, $location = null)
    {
        if ($location != null)
        {
            $service_partners = $this->partnerSelectByLocation($service, $location);
        }
        else
        {
            $service_partners = $this->partnerSelect($service);
        }
        $final_partners = [];
        foreach ($service_partners as $key => $partner)
        {
            $prices = json_decode($partner->prices);
            array_forget($partner, 'pivot');

            if ($service->variable_type == 'Fixed' || $service->variable_type == 'Custom')
            {
                array_set($partner, 'prices', 100);
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
            // review count of this partner for this service
            $review = $partner->reviews()->where([
                ['review', '<>', ''],
                ['service_id', $service->id]
            ])->count('review');
            //avg rating of the partner for this service
            $rating = $partner->reviews()->where('service_id', $service->id)->avg('rating');
            array_add($partner, 'review', $review);
            array_add($partner, 'rating', $rating);
            array_push($final_partners, $partner);
        }
        return $final_partners;

    }

    /**
     * Get partner of a service for a selected option in
     * @param $service
     * @param $option
     * @param $location
     * @return array
     */
    public function partnerWithSelectedOption($service, $option, $location)
    {
        //Get all partners of the service
        $service_partners = $this->partnerSelectByLocation($service, $location);
        $final_partners = [];
        foreach ($service_partners as $key => $partner)
        {
            $options = (array)json_decode($partner->prices);
            //if the selected option exist in partenr option list add the partner to final list
            if (array_has($options, $option))
            {
                //price of the selected option
                $price = array_pull($options, $option);
                array_set($partner, 'prices', $price);
                array_add($partner, 'review', 100);
                array_add($partner, 'rating', 3.5);
                array_forget($partner, 'pivot');
                array_push($final_partners, $partner);
            }
        }
        return $final_partners;
    }

    /**
     * Select partners for a service for a location
     * @param $service
     * @param $location
     * @return mixed
     */
    public function partnerSelectByLocation($service, $location)
    {
        return $service->partners()
            ->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'prices')
            ->whereHas('locations', function ($query) use ($location)
            {
                $query->where('id', $location);
            })
            ->get();
    }

    public function partnerSelect($service)
    {
        return $service->partners()
            ->with(['locations' => function ($query)
            {
                $query->select('id', 'name');
            }])
            ->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'prices')
            ->get();
    }

}