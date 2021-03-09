<?php namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\Division;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\Partner;
use App\Transformers\CustomSerializer;
use App\Transformers\DivisionsWithDistrictsTransformer;
use App\Transformers\DistrictsWithThanasTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Events\OutOfZoneEvent;
use Sheba\Location\Geo;
use stdClass;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $cities = City::whereHas('locations', function ($q) {
            $q->published();
        })->with(['locations' => function ($q) {
            $q->select('id', 'city_id', 'name', 'geo_informations')->hasPolygon()->published();
        }])->select('id', 'name')->get();

        if (count($cities) == 0) return api_response($request, null, 404);

        foreach ($cities as $city) {
            foreach ($city->locations as &$location) {
                $location->center = $location->getCenter()->toStdObject();
                array_forget($location, 'geo_informations');
            }
        }

        return api_response($request, $cities, 200, ['cities' => $cities]);
    }

    public function getAllLocations(Request $request)
    {
        if ($this->isForPartner($request)) {
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
    }

    private function isForPartner(Request $request)
    {
        return ($request->hasHeader('Portal-Name') && $request->header('Portal-Name') == 'manager-app') ||
            ($request->has('for') && $request->for == 'partner');
    }

    public function getCurrent(Request $request, OutOfZoneEvent $event, Geo $geo)
    {
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
        }
        $geo->setLat($request->lat)->setLng($request->lng);
        $event->setGeo($geo)->save();
        return api_response($request, null, 404);
    }

    public function getPartnerServiceLocations(Request $request, $partner)
    {
        $geo_info = json_decode(Partner::find($request->partner)->geo_informations);
        if (!$geo_info) return api_response($request, null, 404);

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
    }

    public function validateLocation(Request $request)
    {
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

        if ($hyper_locations->count() == 0) return api_response($request, null, 400, ['message' => 'Outside service location']);

        return api_response($request, null, 200, ['locations' => $hyper_locations, 'geo_info' => $geo_info]);
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
        if (!json_decode($input_ids)) return [];
        $ids = array_map('intval', json_decode($input_ids));
        if (!$ids) return [];

        $final_services = [];
        $ids = array_map('intval', $ids);
        if (strtolower($model_name) == ('category' || 'service')) {
            $model = "Sheba\\Dal\\" . ucwords($model_name).'\\'.ucwords($model_name);
        } else {
            $model = "App\\Models\\" . ucwords($model_name);
        }
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
        return $final_services;
    }

    public function getDivisionsWithDistrictsAndThana(Request $request)
    {
        $divisions = Division::with('districts.thanas')->get();
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($divisions, new DivisionsWithDistrictsTransformer());
        $formatted_data = $manager->createData($resource)->toArray()['data'];
        return api_response($request, $request, 200, $formatted_data);
    }

    public function getDistrictsWithThanas(Request $request) {
        try {
            $districts = District::with('thanas')->get();
            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($districts, new DistrictsWithThanasTransformer());
            $data = $manager->createData($resource)->toArray()['data'];
            return api_response($request, $request, 200, $data);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, $request, 500);
        }
    }
}
