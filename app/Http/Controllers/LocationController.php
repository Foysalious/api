<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

use App\Http\Requests;

class LocationController extends Controller {
    public function getAllLocations()
    {
        $locations = Location::select('id', 'name')->get();
        return response()->json(['locations' => $locations, 'code' => 200, 'msg' => 'successful']);
    }
}
