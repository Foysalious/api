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
                $model = $grid->item_type::where('id', $grid->item_id)->first();
                $collection = collect($model)->only(['id', 'name', 'icon', 'web_link']);
                $collection->put('item_type', str_replace('App\Models\\', "", $grid->item_type));
                $collection->put('item_id', $grid->item_id);
                array_push($data, $collection);
            }
            return count($data) > 0 ? api_response($request, $data, 200, ['grids' => $data]) : api_response($request, null, 500);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}