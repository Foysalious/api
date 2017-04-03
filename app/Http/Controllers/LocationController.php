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
            ['name', '<>', 'Rest'],
            ['publication_status', 1]
        ])->orderBy('name')->get();
        $rest = Location::select('id', 'name')->where('name', 'Rest')->first();
        $locations->push($rest);
        return response()->json(['locations' => $locations, 'code' => 200, 'msg' => 'successful']);
    }
}
