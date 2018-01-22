<?php

namespace App\Http\Controllers;

use App\Models\Location;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;

use App\Http\Requests;

class LocationController extends Controller
{
    public function getAllLocations()
    {
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
    }

    public function getCurrent(Request $request)
    {
        try {
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);
            $locations = Location::where('name', 'NOT LIKE', '%Rest%')->published()->get();
            $origins = $this->getOriginsForDistanceMatrix($locations);
            if ($result = $this->getDistanceCalculationResult($request->lat, $request->lng, $origins)) {
                if ($result->status == 'OK') {
                    $rows = $result->rows;
                    foreach ($locations as $key => $location) {
                        if ($rows[$key]->elements[0]->status == 'OK') {
                            $location['radius'] = (double)(json_decode($location->geo_informations))->radius;
                            $location['distance'] = round($rows[$key]->elements[0]->distance->value / 1000);
                        } else {
                            unset($locations[$key]);
                        }
                    }
                    $locations = $locations->sortBy('distance')->filter(function ($location, $key) {
                        return $location->distance <= $location->radius;
                    });
                    $location = count($locations) > 0 ? $locations->first() : Location::where('name', 'LIKE', '%Rest%')->published()->first();
                    return api_response($request, $location, 200, ['location' => collect($location)->only(['id', 'name'])]);
                }
            }
            return api_response($request, null, 500, ['result' => $result]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
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

    private function getDistanceCalculationResult($lat, $lng, $origins)
    {
        $client = new Client();
        try {
            $res = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json',
                [
                    'query' => ['origins' => $origins, 'destinations' => "$lat,$lng", 'key' => env('GOOGLE_DISTANCEMATRIX_KEY')]
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return null;
        }
    }

    private function calculateDistance(Request $request, $geo_info)
    {
        $lat1 = $geo_info->lat;
        $lng1 = $geo_info->lng;
        $lat2 = $request->lat;
        $lng2 = $request->lng;

        return ROUND((6371.0 * ACOS(SIN($lat1 * PI() / 180) * SIN($lat2 * PI() / 180) + COS($lat1 * PI() / 180) * COS($lat2 * PI() / 180) * COS(($lng1 * PI() / 180) - ($lng2 * PI() / 180)))), 2);
    }

}
