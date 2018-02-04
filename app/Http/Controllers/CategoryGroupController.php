<?php

namespace App\Http\Controllers;

use App\Models\CategoryGroup;
use App\Sheba\Queries\Category\StartPrice;
use Illuminate\Http\Request;

class CategoryGroupController extends Controller
{
    public function show($id, Request $request)
    {
        try {
            $location = $request->location;
            $category_group = CategoryGroup::with(['categories' => function ($q) {
                $q->select('id', 'name', 'thumb', 'banner', 'parent_id');
            }])->where('id', $id)->first();
            if ($category_group != null) {
                $categories = $category_group->categories->each(function ($category) use ($location) {
                    $start_price = new StartPrice($category, $location);
                    $start_price->calculate();
                    $category['starting_price'] = $start_price->price;
                    removeRelationsAndFields($category);
                });
                if (count($categories) > 0) {
                    return api_response($request, $categories, 200, ['categories' => $categories]);
                }
            }
            return api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}