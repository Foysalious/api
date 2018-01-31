<?php

namespace App\Http\Controllers;

use App\Models\HomeGrid;
use Illuminate\Http\Request;

class HomeGridController extends Controller
{
    public function index(Request $request)
    {
        try {
            $grids = HomeGrid::where('is_published_for_app', 1)->orderBy('order')->get();
            $data = [];
            foreach ($grids as $grid) {
                $model = $grid->typable::where('id', $grid->type_id)->first();
                $collection = collect($model)->only(['id', 'name', 'icon', 'web_link']);
                $collection->put('updated_at_timestamp', $model->updated_at->timestamp);
                array_push($data, $collection);
            }
            return count($data) > 0 ? api_response($request, $data, 200, ['grids' => $data]) : api_response($request, null, 500);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}