<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

class ServiceController extends Controller {
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    public function getPartners($service)
    {
        $service = Service::where('id', $service)
            ->select('id', 'name', 'category_id', 'description', 'thumb', 'banner', 'faqs', 'variable_type', 'variables')
            ->first();
        if ($service->variable_type == 'Options')
        {
            $variables = json_decode($service->variables);
            $first_option = key($variables->prices);
            $first_option = array_map('intval', explode(',', $first_option));
            array_add($service, 'first_option', $first_option);
        }
        array_add($service, 'review', 100);
        array_add($service, 'rating', 3.5);
        if ($service != null)
        {
            $service_partners = $this->serviceRepository->partners($service);
            return response()->json(['service' => $service, 'service_partners' => $service_partners, 'msg' => 'successful', 'code' => 200]);
        }
        return response()->json(['msg' => 'no result found', 'code' => 404]);
    }

    public function changePartner($service, Request $request)
    {
        $resultPartner = [];
        $partners = $request->input('partners');
        $options = implode(',', $request->input('options'));
        for ($i = 0; $i < count($partners); $i++)
        {
            $price_options = $partners[$i]['price_option'];
            //check if selected option exist in price_options list
            if (array_has($price_options, $options))
            {
                array_push($resultPartner, $partners[$i]);
            }

        }
        return response($resultPartner);
    }

}
