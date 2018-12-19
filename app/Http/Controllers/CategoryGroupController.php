<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\HomepageSetting;
use App\Sheba\Queries\Category\StartPrice;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryGroupController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'sometimes|required|string|in:app,web',
                'name' => 'sometimes|required|string',
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $location = null;
            if($request->has('location') ) {
                $location = Location::find($request->location)->id;
            } else if($request->has('lat')) {
                $hyperLocation= HyperLocal::insidePolygon((double) $request->lat, (double)$request->lng)->with('location')->first();
                if(!is_null($hyperLocation)) $location = $hyperLocation->location->id;
            }
            $location = !is_null($location) ? $location: 4;
            $for = $this->getPublishedFor($request->for);
            if ($request->has('name')) {
                $categories = $this->getCategoryByColumn('name', $request->name, $location);
                return $categories ? api_response($request, $categories, 200, ['category' => $categories]) : api_response($request, null, 404);
            }
            $with = '';
            $categoryGroups = CategoryGroup::$for()->select('id', 'name', 'app_thumb', 'app_banner')
                             ->whereHas('locations',function($query) use ($location) {
                                 $query->where('locations.id',$location);
                             })
                            ->get();

            if ($request->has('with')) {
                $with = $request->with;
                if ($with == 'categories') {
                    $categoryGroups->load(['categories' => function ($query) {
                        return $query;
                    }]);
                    $categoryGroups = $categoryGroups->each(function ($category_group) {
                        $category_group->categories->each(function ($category) {
                            removeRelationsAndFields($category);
                        });
                        $category_group->children = $category_group->categories;
                        unset($category_group->categories);
                    });
                }
            }
            return count($categoryGroups) > 0 ? api_response($request, $categoryGroups, 200, ['categories' => $categoryGroups]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
            $category_group = CategoryGroup::with('categories')->where('id', $id)->select('id', 'name')->first();
            if ($category_group != null) {
                $categories = $category_group->categories->each(function ($category) use ($location) {
                    removeRelationsAndFields($category);
                });
                if (count($categories) > 0) {
                    $category_group['position_at_home'] = null;
                    removeRelationsAndFields($category_group);
                    $category_group['secondaries'] = $categories;
                    return api_response($request, $categories, 200, ['category' => $category_group]);
                }
            }
            return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getCategoryByColumn($column, $value, $location)
    {
        $category_group = CategoryGroup::with(['categories' => function ($q) {
            $q->has('services', '>', 0);
        }])->where($column, 'like', '%' . $value . '%')->select('id', 'name', 'banner')->first();
        if ($category_group != null) {
            $setting = HomepageSetting::where([['item_type', 'App\\Models\\CategoryGroup'], ['item_id', $category_group->id]])->first();
            if ($setting != null) {
                $category_group['position_at_home'] = $setting ? $setting->order : null;
                $categories = $category_group->categories->each(function ($category) use ($location) {
                    removeRelationsAndFields($category);
                });
                if (count($categories) > 0) {
                    $category_group['secondaries'] = $categories;
                    removeRelationsAndFields($category_group);
                    return $category_group;
                }
            }
        }
        return null;
    }
}