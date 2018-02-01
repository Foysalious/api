<?php

namespace App\Http\Controllers;


use App\Models\CategoryGroup;
use Illuminate\Http\Request;

class CategoryGroupController extends Controller
{

    public function show($id, Request $request)
    {
        try {
            $category_group = CategoryGroup::with(['categories' => function ($q) {
                $q->select('id', 'name', 'thumb', 'banner');
            }])->where('id', $id)->first();
            if ($category_group != null) {
                $categories = $category_group->categories->each(function ($category) {
                    $category['starting_price'] = 500;
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