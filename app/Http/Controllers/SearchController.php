<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    public function getService(Request $request)
    {
        if ($request->input('s') != '') {
            $query = Service::where('name', 'like', "%" . $request->input('s') . "%");
            //if has parent category id
            if ($request->has('p_c')) {
                $category = Category::find($request->input('p_c'));
                $children_categories = $category->children()->pluck('id');
                $query = $query->whereIn('category_id', $children_categories);
            }
            $services = $query->where('publication_status', 1)
                ->select('id', 'name', 'thumb', 'banner', 'variables', 'variable_type')
                ->take(10)
                ->get();

            if ($services->isEmpty())
                return response()->json(['msg' => 'nothing found', 'code' => 404]);
            else {
                foreach ($services as $service) {
                    array_add($service, 'slug_service', str_slug($service->name));
                    //if service has no partners
                    if ($service->partners->isEmpty()) {
                        array_add($service, 'review', 0);
                        array_add($service, 'rating', 0);
                        array_add($service, 'start_price', 0);
                        array_add($service, 'end_price', 0);
                        continue;
                    }
                    $service = $this->serviceRepository->getStartEndPrice($service);
                    // review count of this partner for this service
                    $review = $service->reviews()->where('review', '<>', '')->count('review');
                    //avg rating of the partner for this service
                    $rating = $service->reviews()->avg('rating');
                    array_add($service, 'review', $review);
                    array_add($service, 'rating', $rating);
                }
            }
            return response()->json(['msg' => 'successful', 'code' => 200, 'services' => $services]);
        } else
            return response()->json(['msg' => 'nothing found', 'code' => 404]);

    }
}
