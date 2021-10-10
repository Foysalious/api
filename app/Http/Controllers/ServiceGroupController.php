<?php namespace App\Http\Controllers;

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
use Sheba\Dal\Service\Service;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\Dal\LocationService\LocationService;

class ServiceGroupController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'location' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'required_with:lat',
            'for' => 'sometimes'
        ]);
        $location = null;
        if ($request->has('location')) {
            $location = Location::find($request->location)->id;
        } else if ($request->has('lat')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
        }
        $service_group_list = [];
        if ($location) {
            $service_groups = ServiceGroup::select('id', 'name', 'thumb', 'app_thumb', 'short_description')->publishedFor($request->for)->with([
                'services' => function ($query) use ($location) {
                    $query->select('id', 'category_id', 'name', 'thumb', 'app_thumb')->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location);
                    })->published();
                }
            ])->whereHas('locations', function ($q) use ($location) {
                $q->where('locations.id', $location);
            })->get();
        } else {
            $service_groups = ServiceGroup::select('id', 'name', 'thumb', 'app_thumb', 'short_description')->publishedFor($request->for)->with([
                'services' => function ($query){
                    $query->select('id', 'category_id', 'name', 'thumb', 'app_thumb')->published();
                }
            ])->get();
        }

        if (count($service_groups) === 0)
            return api_response($request, 1, 404);
        $service_groups->each(function ($service_group) use (&$service_group_list,$location) {
            $services = $service_group->services;
            $services_without_pivot_data = $services->each(function ($service) use($location) {
                if ($location) {
                    $location_service = LocationService::where('location_id', $location)->where('service_id', $service->id)->first();
                    $service_discount = $location_service->discounts()->running()->first();
                    removeRelationsFromModel($service);
                    $service['slug'] = $service->getSlug();
                    $service['universal_slug'] = $service->getSlug();
                    $service['has_discount'] = $service_discount ? 1 : 0;
                    $service['discount_amount'] = $service_discount ? $service_discount['amount'] : 0;
                }
                else {
                    removeRelationsFromModel($service);
                    $service['slug'] = $service->getSlug();
                    $service['universal_slug'] = $service->getSlug();
                }
            });
            array_push($service_group_list, [
                'id' => $service_group->id,
                'name' => $service_group->name,
                'thumb' => $service_group->thumb,
                'app_thumb' => $service_group->app_thumb,
                'short_description' => $service_group->short_description,
                'services' => $services_without_pivot_data

            ]);
        });
        return api_response($request, null, 200, ['service_groups' => $service_group_list]);
    }

    public function show($service_group, Request $request)
    {
        $single_service_group = ServiceGroup::find($service_group);
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

        if(is_null($single_service_group->locations()->where('location_id',$location)->first())) return api_response($request, 1, 404);

        if ($location) {
            $loc_is_published = Location::find($location)->publication_status;
            if ($loc_is_published==1 && $single_service_group->is_published_for_app==1 && $single_service_group->is_published_for_web==1) {
                $service_group = ServiceGroup::with(['services' => function ($q) use ($location) {
                    return $q->published()
                        ->whereHas('locations', function ($q) use ($location) {
                            $q->where('locations.id', $location);
                        })->orderBy('stock_left');
                }])->where('id', $service_group)->select('id', 'name', 'app_thumb')->first();
            } else {
                return api_response($request, 1, 404);
            }
        } else {
            if ($single_service_group->is_published_for_app==1 && $single_service_group->is_published_for_web==1) {
                $service_group = ServiceGroup::with(['services' => function ($q) {
                    $q->published()/*->orderBy('service_group_service.order')*/ ->orderBy('stock_left');
                }])->where('id', $service_group)->select('id', 'name', 'app_thumb')->first();
            } else {
                return api_response($request, 1, 404);
            }
        }

        if (!$service_group) return api_response($request, 1, 404);

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
            $service = $this->formatService($service);
            if ($location) {
                $location_service = LocationService::where('location_id', $location)->where('service_id', $service['id'])->first();
                $service_discount = $location_service->discounts()->running()->first();
                $service['has_discount'] = $service_discount ? 1 : 0;
            }
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
    }

    private function formatService(Service $service)
    {
        return [
            'master_category_id' => $service->category->parent->id,
            'category_name' => $service->category->parent->name,
            "id" => $service->id,
            "service_name" => $service->name,
            'image' => $service->app_thumb,
            'app_thumb' => $service->app_thumb,
            'app_thumb_sizes' => getResizedUrls($service->app_thumb, 100, 100),
            'thumb' => $service->thumb,
            'thumb_sizes' => getResizedUrls($service->thumb, 180, 270),
            'total_stock' => (int)$service->stock,
            'stock_left' => (int)$service->stock_left,
            'slug' => $service->getSlug()
        ];
    }

}
