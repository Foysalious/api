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
            foreach ($locations as $location) {
                $geo_info = json_decode($location->geo_informations);
                $location['radius'] = (double)$geo_info->radius * 1000;
                $location['distance'] = $this->calculateDistanceByGoogle($request, $geo_info);
//                $location['distance'] = $this->calculateDistance($request, $geo_info) * 1000;
            }
            $locations = $locations->sortBy('distance')->filter(function ($location, $key) {
                return $location->distance <= $location->radius;
            });
            if (count($locations) > 0) {
                $location = $locations->first();
            } else {
                $location = Location::where('name', 'LIKE', '%Rest%')->published()->first();
            }
            return api_response($request, $location, 200, ['location' => collect($location)->only(['id', 'name'])]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
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

    private function calculateDistanceByGoogle(Request $request, $geo_info)
    {
        $client = new Client();
        try {
            $res = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json',
                [
                    'query' => ['origins' => "$geo_info->lat,$geo_info->lng", 'destinations' => "$request->lat,$request->lng", 'key' => 'AIzaSyB5FJ8GazV8DOtjmqBc_Xy1MN1gOGEksQA']
                ]);
            $result = json_decode($res->getBody());
            if ($result->status == "OK") {
                $rows = ($result->rows[0]);
                foreach ($rows as $row) {
                    if ($row[0]->status == 'OK') {
                        return (double)$row[0]->distance->value;
                        break;
                    }
                }
                return null;
            }
        } catch (RequestException $e) {
            return null;
        }
    }
}
