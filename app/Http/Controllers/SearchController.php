<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

use App\Http\Requests;

class SearchController extends Controller {
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    public function getService(Request $request)
    {
        $services = Service::where('name', 'like', "%" . $request->get('key') . "%")
            ->with(['reviews' => function ($query)
            {
                $query->select('service_id')->selectRaw('count(*) as reviews,avg(rating) as rating');
            }])
            ->select('id', 'name', 'thumb', 'variable_type')
            ->get();
        foreach ($services as $service)
        {
            if ($service->variable_type != 'Custom')
            {
                $maxMinPrice=$this->serviceRepository->getMaxMinPrice($service);
                array_add($service,'start_price',$maxMinPrice[1]);
                array_add($service,'end_price',$maxMinPrice[0]);
            }
        }
        if (!$services->isEmpty())
            return response()->json(['msg' => 'successful', 'code' => 200, 'services' => $services]);
        else
            return response()->json(['msg' => 'nothing found', 'code' => 404]);
    }
}
