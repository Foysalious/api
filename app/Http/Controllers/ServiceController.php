<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\PartnerService;
use App\Models\Service;
use App\Repositories\RatingReviewRepository;
use App\Repositories\ReviewRatingRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

class ServiceController extends Controller {
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    public function getPartners($service, $location)
    {
        $service = Service::where('id', $service)
            ->select('id', 'name', 'category_id', 'description', 'thumb', 'banner', 'faqs', 'variable_type', 'variables')
            ->first();

        //Add first options in service for render purpose
        if ($service->variable_type == 'Options')
        {
            $variables = json_decode($service->variables);
            $first_option = key($variables->prices);
            $first_option = array_map('intval', explode(',', $first_option));
            array_add($service, 'first_option', $first_option);
        }
        // review count of this service
        $review = $service->reviews()->where('review', '<>', '')->count('review');
        //avg rating of this service
        $rating = $service->reviews()->avg('rating');
        array_add($service, 'review', $review);
        array_add($service, 'rating', $rating);

        //get partners of the service
        $service_partners = $this->serviceRepository->partners($service, $location);
        $service->variables = json_decode($service->variables);
        //If service has partner
        if (count($service_partners) != 0)
        {
            return response()->json(['service' => $service, 'service_partners' => $service_partners, 'msg' => 'successful', 'code' => 200]);
        }
        return response()->json(['service' => $service, 'service_partners' => $service_partners, 'msg' => 'no partner found in selected location', 'code' => 404]);
    }

    /**
     * Change partner according to the selected options
     * @param Service $service
     * @param $location
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePartner(Service $service, $location, Request $request)
    {
        //get the selected options
        $option = implode(',', $request->input('options'));
        //check if any partner provide service in the location
        $service_partners = $this->serviceRepository->partnerWithSelectedOption($service, $option, $location);
        if (!empty($service_partners))
        {
            return response()->json(['service_partners' => $service_partners, 'msg' => 'successful', 'code' => 200]);
        }
        else
            return response()->json(['msg' => 'no partner found', 'code' => 404]);
    }

}
