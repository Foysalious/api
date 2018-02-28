<?php

namespace App\Http\Controllers;

use App\Models\HomeGrid;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HomeGridController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'required|string|in:app,web'
            ]);
            $for = $this->getPublishedFor($request->for);
            $grids = HomeGrid::$for()->orderBy('order')->get();
            $data = [];
            foreach ($grids as $grid) {
                $model = $grid->item_type::where('id', $grid->item_id)->first();
                $collection = collect($model)->only(['id', 'name', 'icon', 'web_link']);
                $collection->put('item_type', str_replace('App\Models\\', "", $grid->item_type));
                $collection->put('item_id', $grid->item_id);
                if (!$collection->has('web_link')) {
                    $collection->put('web_link', null);
                }
                array_push($data, $collection);
            }
            return count($data) > 0 ? api_response($request, $data, 200, ['grids' => $data]) : api_response($request, null, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function getPublishedFor($for)
    {
        return 'publishedFor' . ucwords($for);
    }
}