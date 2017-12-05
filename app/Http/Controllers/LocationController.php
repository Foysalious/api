<?php

namespace App\Http\Controllers;

use App\Models\Location;
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
}
