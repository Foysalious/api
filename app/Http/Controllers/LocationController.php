<?php namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Division;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\Partner;
use App\Transformers\CustomSerializer;
use App\Transformers\DivisionsWithDistrictsTransformer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Events\OutOfZoneEvent;
use Sheba\Location\Geo;
use stdClass;
use Throwable;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cities = City::whereHas('locations', function ($q) {
                $q->published();
            })->with(['locations' => function ($q) {
                $q->select('id', 'city_id', 'name', 'geo_informations')->hasPolygon()->published();
            }])->select('id', 'name')->get();
            foreach ($cities as $city) {
                foreach ($city->locations as &$location) {
                    if ($location->geo_informations) {
                        $geo = json_decode($location->geo_informations);
                        $location->center = isset($geo->center) ? $geo->center : json_decode(json_encode(['lat' => (double)$geo->lat, 'lng' => (double)$geo->lng]));
                        array_forget($location, 'geo_informations');
                    }
                }
            }
            if (count($cities) > 0) {
                return api_response($request, $cities, 200, ['cities' => $cities]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAllLocations(Request $request)
    {
        try {
            if (($request->hasHeader('Portal-Name') && $request->header('Portal-Name') == 'manager-app') || ($request->has('for') && $request->for == 'partner')) {
                $locations = Location::select('id', 'name')->where('is_published_for_partner', 1)->orderBy('name')->get();
                return response()->json(['locations' => $locations, 'code' => 200, 'msg' => 'successful']);
            }
            $locations = Location::select('id', 'name', 'geo_informations')->where([
                ['name', 'NOT LIKE', '%Rest%'],
                ['publication_status', 1],
                ['geo_informations', 'LIKE', '%polygon%']
            ])->orderBy('name')->get()->each(function ($location) {
                array_forget($location, 'geo_informations');
            });
            return response()->json(['locations' => $locations, 'code' => 200, 'msg' => 'successful']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function getCurrent(Request $request, OutOfZoneEvent $event, Geo $geo)
    {
        try {
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'service' => 'string',
                'category' => 'string',
            ]);
            $hyper_locals = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->get()->filter(function ($hyper_local) {
                return $hyper_local->location->isPublished();
            });
            if (count($hyper_locals) > 0) {
                $hyper_local = $hyper_locals->first();
                $location = $hyper_local->location;
                return api_response($request, $location, 200,
                    [
                        'location' => collect($location)->only(['id', 'name']),
                        'service' => $request->has('service') ? $this->calculateModelAvailability($request->service, 'Service', $location) : [],
                        'category' => $request->has('category') ? $this->calculateModelAvailability($request->category, 'Category', $location) : [],
                    ]);
            } else {
                $geo->setLat($request->lat)->setLng($request->lng);
                $event->setGeo($geo)->save();
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }

    public function getPartnerServiceLocations(Request $request, $partner)
    {
        $geo_info = json_decode(Partner::find($request->partner)->geo_informations);
        if ($geo_info) {
            $hyper_locations = HyperLocal::insideCircle($geo_info)
                ->with('location')
                ->get()
                ->filter(function ($item) {
                    return !empty($item->location);
                })->pluck('location');
            $hyper_locations = $hyper_locations->map(function ($location) {
                unset($location->geo_informations);
                return $location;
            });
            return api_response($request, null, 200, ['locations' => $hyper_locations, 'geo_info' => $geo_info]);
        } else {
            return api_response($request, null, 404);
        }
    }

    public function validateLocation(Request $request)
    {
        try {
            $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric', 'radius' => 'required|numeric']);
            $geo_info = new stdClass();
            $geo_info->lat = $request->lat;
            $geo_info->lng = $request->lng;
            $geo_info->radius = $request->radius;
            $hyper_locations = HyperLocal::insideCircle($geo_info)
                ->with('location')
                ->get()
                ->filter(function ($item) {
                    return !empty($item->location);
                })->pluck('location');
            if ($hyper_locations->count() > 0) {
                return api_response($request, null, 200, ['locations' => $hyper_locations, 'geo_info' => $geo_info]);
            } else {
                return api_response($request, null, 400, ['message' => 'Outside service location']);
            }
        } catch (ValidationException $e) {
            return api_response($request, $request, 400, ['message' => getValidationErrorMessage($e->validator->messages()->all())]);
        }
    }

    private function getOriginsForDistanceMatrix($locations)
    {
        $origins = '';
        foreach ($locations as $location) {
            $geo_info = json_decode($location->geo_informations);
            $origins .= "$geo_info->lat,$geo_info->lng|";
        }
        return rtrim($origins, "|");
    }

    /**
     * @param $input_ids
     * @param $model_name
     * @param $location
     * @return array
     */
    private function calculateModelAvailability($input_ids, $model_name, $location)
    {
        $final_services = [];
        $ids = json_decode($input_ids);
        if ($ids) {
            $ids = array_map('intval', $ids);
            $model = "App\\Models\\" . ucwords($model_name);
            $models = $model::whereIn('id', $ids)->whereHas('locations', function ($q) use ($location) {
                $q->where('locations.id', $location->id);
            });
            $models = $models->get();
            if ($model_name == 'Category') {
                $models = $models->load(['children' => function ($q) use ($location) {
                    $q->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location->id);
                    });
                }, 'services' => function ($q) use ($location) {
                    $q->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location->id);
                    });
                }]);
                $models = $models->filter(function ($category) use ($location) {
                    $children = $category->isParent() ? $category->children : $category->services;
                    foreach ($children as $child) {
                        if (in_array($location->id, $child->locations->pluck('id')->toArray())) {
                            return true;
                        }
                    }
                    return false;
                });
            }
            foreach ($ids as $id) {
                array_push($final_services, ['id' => (int)$id, 'is_available' => $models->where('id', $id)->first() ? 1 : 0]);
            }
        }
        return $final_services;
    }

    public function getDivisionsWithDistrictsAndThana(Request $request)
    {
        try {
            $divisions = Division::with('districts.thanas')->get();
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($divisions, new DivisionsWithDistrictsTransformer());
            $formatted_data = $manager->createData($resource)->toArray()['data'];
            return api_response($request, $request, 200, $formatted_data);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, $request, 500);
        }
    }
}
