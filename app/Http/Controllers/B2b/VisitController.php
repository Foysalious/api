<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function getTeamVisits(Request $request)
    {
        dd($request->all());
    }

    public function getMyVisits(Request $request)
    {
        dd($request->all());
    }
}