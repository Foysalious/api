<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use MongoDB\Collection;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cities = City::whereHas('locations', function ($q) {
                $q->published();
            })->with(['locations' => function ($q) {
                $q->select('id', 'city_id', 'name', 'geo_informations');
            }])->select('id', 'name')->get();
            foreach ($cities as $city) {
                foreach ($city->locations as &$location) {
                    if ($location->geo_informations) {
                        $geo = json_decode($location->geo_informations);
                        $location->center = isset($geo->center) ? $geo->center : null;
                        array_forget($location, 'geo_informations');
                    }
                }
            }
            if (count($cities) > 0) {
                return api_response($request, $cities, 200, ['cities' => $cities]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
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
            $locations = Location::select('id', 'name')->where([
                ['name', 'NOT LIKE', '%Rest%'],
                ['publication_status', 1]
            ])->orderBy('name')->get();

            Location::select('id', 'name')->where([
                ['name', 'LIKE', '%Rest%'],
                ['publication_status', 1]
            ])->get()->each(function ($location, $key) use ($locations) {
                $locations->push($location);
            });
            return response()->json(['locations' => $locations, 'code' => 200, 'msg' => 'successful']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function getCurrent(Request $request)
    {
        try {
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'service' => 'string',
                'category' => 'string',
            ]);
            $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if ($hyper_local) {
                $location = $hyper_local->location;
                return api_response($request, $location, 200,
                    [
                        'location' => collect($location)->only(['id', 'name']),
                        'service' => $request->has('service') ? $this->calculateModelAvailability($request->service, 'Service', $location) : [],
                        'category' => $request->has('category') ? $this->calculateModelAvailability($request->category, 'Category', $location) : [],
                    ]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPartnerServiceLocations(Request $request, $partner)
    {
        $geo_info = json_decode(Partner::find($request->partner)->geo_informations);
        if ($geo_info) {
            $hyper_locations = HyperLocal::insideCircle($geo_info)->get();
            $locations = HyperLocal::with('location')->get();
            return api_response($request, null, 200, ['locations' => $hyper_locations, 'all' => $locations]);
        } else {
            return api_response($request, null, 404);
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
            $model_name = "App\\Models\\" . ucwords($model_name);
            $models = $model_name::whereIn('id', $ids)->whereHas('locations', function ($q) use ($location) {
                $q->where('locations.id', $location->id);
            })->get();
            foreach ($ids as $id) {
                array_push($final_services, ['id' => (int)$id, 'is_available' => $models->where('id', $id)->first() ? 1 : 0]);
            }
        }
        return $final_services;
    }
}
