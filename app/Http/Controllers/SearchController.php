<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Input;

class SearchController extends Controller {
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    public function getService(Request $request)
    {
        if ($request->input('s') != '')
        {
            $query = Service::where('name', 'like', "%" . $request->input('s') . "%");
            //if has parent category id
            if ($request->has('p_c'))
            {
                $category = Category::find($request->input('p_c'));
                $children_categories = $category->children()->pluck('id');
                $query = $query->whereIn('category_id', $children_categories);
            }
            $services = $query->select('id', 'name', 'thumb', 'banner', 'variable_type')->get();

            if ($services->isEmpty())
                return response()->json(['msg' => 'nothing found', 'code' => 404]);
            else
            {
                foreach ($services as $service)
                {
                    if ($service->variable_type != 'Custom')
                    {
                        $maxMinPrice = $this->serviceRepository->getMaxMinPrice($service);
                        array_add($service, 'start_price', $maxMinPrice[1]);
                        array_add($service, 'end_price', $maxMinPrice[0]);
                    }
                    // review count of this partner for this service
                    $review = $service->reviews()->where('review', '<>', '')->count('review');
                    //avg rating of the partner for this service
                    $rating = $service->reviews()->avg('rating');
                    array_add($service, 'review', $review);
                    array_add($service, 'rating', $rating);
                }
            }
            return response()->json(['msg' => 'successful', 'code' => 200, 'services' => $services]);
        }
        else
            return response()->json(['msg' => 'nothing found', 'code' => 405]);

    }
}
