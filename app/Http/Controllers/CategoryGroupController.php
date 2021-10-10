<?php namespace App\Http\Controllers;

use App\Models\CategoryGroup;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\ScreenSettingElement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\CategoryGroup\CategoryGroupCache;
use Sheba\Location\LocationSetter;
use Sheba\Recommendations\HighlyDemands\Categories\Identifier;
use Sheba\Recommendations\HighlyDemands\Categories\Recommender;
use Throwable;

class CategoryGroupController extends Controller
{
    use LocationSetter;

    public function index(Request $request, Recommender $recommender, CacheAside $cacheAside, CategoryGroupCache $category_group_cache)
    {
        $this->validate($request, [
            'for' => 'sometimes|required|string|in:app,web',
            'name' => 'sometimes|required|string',
            'location' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'required_with:lat',
        ]);

        $for = $this->getPublishedFor($request->for);
        if ($request->has('name') && $request->name == Identifier::HIGH_DEMAND) {
            $location_id = $request->has('location_id') ? $request->location_id : $this->location;
            $secondaries = $recommender->setParams(Carbon::now())->setLocationId($location_id)->get();
            return api_response($request, null, 200, [
                'category' => [
                    'name' => 'Recommended',
                    'secondaries' => $secondaries
                ]
            ]);
        }
        if ($request->has('name')) {
            $categories = $this->getCategoryByColumn('name', $request->name, $this->location);
            return $categories ? api_response($request, $categories, 200, ['category' => $categories]) : api_response($request, null, 404);
        }
        $categoryGroups = CategoryGroup::$for()->select('id', 'name','icon','icon_png')
            ->hasLocation($this->location)
            ->get();
        if ($request->has('with') && $request->with == 'categories') {
            $categoryGroups->load(['categories' => function ($query) {
                $query->publishedWithServiceOnLocation($this->location)->orderBy('category_group_category.order');
                if (\request()->has('new')) $query->select('id', 'name', 'thumb', 'app_thumb', 'icon_png', 'icon_png_active');
            }]);
            $categoryGroups = $categoryGroups->each(function ($category_group) {
                $category_group->categories->each(function ($category) {
                    $category->thumb_sizes = getResizedUrls($category->thumb, 180, 270);
                    removeRelationsAndFields($category);
                });
                $category_group->children = $category_group->categories;
                unset($category_group->categories);
            })->filter(function ($category_group) {
                return !$category_group->children->isEmpty();
            })->values();
        }

        if (count($categoryGroups) > 0) {
            $categoryGroups->map(function ($category_group) {
                $category_group->icon_png_active = "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/images/category_images/default_icons/active_v3.png";
            });
            return api_response($request, $categoryGroups, 200, ['categories' => $categoryGroups]);
        }

        return api_response($request, null, 404);
    }

    private function getPublishedFor($for)
    {
        return $for == null ? 'publishedForWeb' : 'publishedFor' . ucwords($for);
    }

    public function show($id, Request $request)
    {
        $this->validate($request, [
            'location' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'required_with:lat'
        ]);
        $location = null;
        if ($request->has('location')) {
            $location = Location::find($request->location)->id;
        } else if ($request->has('lat')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
        }

        $category_group = CategoryGroup::select('id', 'name', 'icon_png', 'short_description', 'thumb', 'banner')
            ->with(['categories' => function ($q) use ($location) {
                $q->publishedWithServiceOnLocation($location)->orderBy('category_group_category.order');
            }])->where('id', $id)->first();

        if ($category_group == null) return api_response($request, null, 404);

        $categories = $category_group->categories->each(function ($category) use ($location) {
            $category->thumb_sizes = getResizedUrls($category->thumb, 180, 270);
            $category->slug = $category->getSlug();
//            dump($category->slug);
            removeRelationsAndFields($category);
        });

        if (count($categories) == 0) return api_response($request, null, 404);

        $category_group['position_at_home'] = null;
        removeRelationsAndFields($category_group);
        $category_group['secondaries'] = $categories;
        return api_response($request, $categories, 200, ['category' => $category_group]);
    }

    public function getCategoryByColumn($column, $value, $location)
    {
        $category_group = CategoryGroup::with(['categories' => function ($q) {
            $q->has('services', '>', 0);
        }])->where($column, 'like', '%' . $value . '%')->select('id', 'name', 'banner')->first();
        if ($category_group == null) return null;

        $setting = $category_group->screenSettingElements()->first();
        if ($setting == null) return null;

        $category_group['position_at_home'] = $setting ? $setting->order : null;
        $categories = collect();
        $category_group->categories->each(function ($category) use ($location, $categories) {
            if (in_array($location, $category->locations()->pluck('id')->toArray())) {
                $category->thumb_sizes = getResizedUrls($category->thumb, 180, 270);
                $categories->push($category);
                removeRelationsAndFields($category);
            }
        });
        if (count($categories) > 0) {
            $category_group['secondaries'] = $categories;
            removeRelationsAndFields($category_group);
            return $category_group;
        }
    }
}
