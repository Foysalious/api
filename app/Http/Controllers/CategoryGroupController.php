<?php

namespace App\Http\Controllers;

use App\Models\CategoryGroup;
use App\Sheba\Queries\Category\StartPrice;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;

class CategoryGroupController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'sometimes|required|string|in:app,web'
            ]);
            $for = $this->getPublishedFor($request->for);
            $categoryGroups = CategoryGroup::$for()->select('id', 'name', 'app_thumb', 'app_banner')->get();
            return count($categoryGroups) > 0 ? api_response($request, $categoryGroups, 200, ['category_groups' => $categoryGroups]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function getPublishedFor($for)
    {
        return $for == null ? 'publishedForWeb' : 'publishedFor' . ucwords($for);
    }

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