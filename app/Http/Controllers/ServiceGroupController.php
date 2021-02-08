<?php namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\CategoryGroup;
use App\Models\HomepageSetting;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\OfferGroup;
use App\Models\OfferShowcase;
use App\Models\ScreenSettingElement;
use App\Models\ServiceGroup;
use App\Sheba\Queries\Category\StartPrice;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceGroupController extends Controller
{
    public function index(Request $request)
    {
        $service_group_list = [];

        $service_groups = ServiceGroup::select('id','name', 'thumb', 'app_thumb','short_description')->with(['services' => function ($query) {
            $query->select('id', 'category_id', 'name', 'thumb', 'app_thumb')->published();
        }])->get();

        if(count($service_groups) === 0) return api_response($request, 1, 404);

        $service_groups->each(function ($service_group) use (&$service_group_list){
            $services = $service_group->services->toArray();

            array_push($service_group_list, [
                'id' => $service_group->id,
                'name' => $service_group->name,
                'thumb' => $service_group->thumb,
                'app_thumb' => $service_group->app_thumb,
                'short_description' => $service_group->short_description,
                'services' => $services
            ]);
            foreach($services as $service){
                unset($service['pivot']);
            }

        });


        return api_response($request, $service_group_list, 200, ['service_groups' => $service_group_list]);
    }
    public function show($service_group, Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location)->id;
            } else if ($request->has('lat')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
            }


            if ($location) {
                $service_group = ServiceGroup::with(['services' => function ($q) use ($location) {
                    return $q->published()/*->orderBy('service_group_service.order')
                        ->whereHas('locations', function ($q) use ($location) {
                            $q->where('locations.id', $location);
                        });*/->orderBy('stock_left');
                }])->where('id', $service_group)->select('id', 'name', 'app_thumb')->first();
            } else {
                $service_group = ServiceGroup::with(['services' => function ($q) {
                    $q->published()/*->orderBy('service_group_service.order')*/ ->orderBy('stock_left');
                }])->where('id', $service_group)->select('id', 'name', 'app_thumb')->first();
            }

            if ($service_group) {
                $offer_group_id = null;
                $offer = OfferShowcase::targetType('ServiceGroup')->where('target_id', $service_group->id)->active()->valid()->orderBy('end_date')->first();
                if ($offer) {
                    $offer->load(['groups' => function ($q) use ($location) {
                        $q->select('id');
                        if ($location) {
                            $q->whereHas('locations', function ($q) use ($location) {
                                $q->where('locations.id', $location);
                            });
                        }
                    }]);
                    if ($offer->groups->first()) $offer_group_id = $offer->groups->first()->id;
                }
                $services = [];
                $service_group->services->load('category.parent');
                foreach ($service_group->services as $service) {
                    //$service_variable = $service->flashPrice();
                    $service = [
                        'master_category_id' => $service->category->parent->id,
                        'category_name' => $service->category->parent->name,
                        "id" => $service->id,
                        "service_name" => $service->name,
                        'image' => $service->app_thumb,
                        "original_price" => 1000,
                        "discounted_price" => 500,
                        "discount" => 50,
                        'total_stock' => (int)$service->stock,
                        'stock_left' => (int)$service->stock_left
                    ];
                    array_push($services, $service);
                }

                $service_group = [
                    'id' => $service_group->id,
                    "name" => $service_group->name,
                    "app_thumb" => $service_group->app_thumb,
                    "services" => $services,
                    'offer_group_id' => $offer_group_id
                ];
                $master_category = collect($services)->unique('master_category_id')->map(function ($item) {
                    return ['id' => $item['master_category_id'], 'name' => $item['category_name']];
                })->values();
                return api_response($request, $service_group, 200, ['service_group' => $service_group, 'master_category' => $master_category]);
            } else {
                return api_response($request, 1, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}