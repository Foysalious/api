<?php namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\CategoryGroup;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\OfferShowcase;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Sheba\AppSettings\HomePageSetting\DS\Builders\ItemBuilder;
use Throwable;

class HomePageSettingController extends Controller
{
    public function index(Request $request)
    {
        $portals = config('sheba.portals');
        $screens = config('sheba.screen');
        $this->validate($request, [
            'for' => 'string|in:app,web,app_json,app_json_revised',
            'portal' => 'in:' . implode(',', $portals),
            'screen' => 'in:' . implode(',', $screens),
            'location' => 'numeric',
            'lat' => 'numeric',
            'lng' => 'numeric'
        ]);
        $setting_key = null;
        $location = 4;

        if ($request->has('location')) {
            $location = (int)$request->location;
        } elseif ($request->has('lat') && $request->has('lng')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
        }
        $city = (Location::find($location))->city_id;
        $location_id = ($city == 1) ? 4 : 120;
        $portal = ($request->get('portal') == 'customer-app') ? 'app' : 'web';

        $settings = file_get_contents(base_path() . '/public/screen_setting/screen_setting_' . $portal . '_' . $location_id . '.json');

        if (!$settings) return api_response($request, null, 404);

        $settings = json_decode($settings);
        if ($request->portal == 'customer-portal') $settings = $this->formatWeb($settings, $location);
        return api_response($request, $settings, 200, ['settings' => $settings]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function indexNew(Request $request)
    {
        /** @var Repository $store */
        $store = Cache::store('redis');
        $portals = config('sheba.portals');
        $screens = config('sheba.screen');
        $platforms = constants('DEVELOPMENT_PLATFORMS');
        $this->validate($request, [
            'for' => 'string|in:app,web,app_json,app_json_revised',
            'portal' => 'in:' . implode(',', $portals),
            'screen' => 'in:' . implode(',', $screens),
            'location' => 'numeric',
            'lat' => 'numeric',
            'platform' => 'sometimes|in:' . implode(',', $platforms),
            'lng' => 'numeric'
        ]);
        $setting_key = null;
        $location = '';
        if ($request->has('location')) {
            $location = (int)$request->location;
        } elseif ($request->has('lat') && $request->has('lng')) {
            $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyperLocation)) $location = $hyperLocation->location_id;
        }
        if ($request->has('portal') && $request->has('screen')) {
            $platform = $this->getPlatform($request);
            $setting_key = 'NewScreenSetting::' . snake_case(camel_case($request->portal)) . '_' . $request->screen . "_" . strtolower($platform) . "_" . $location;
        } else {
            $setting_key = 'NewScreenSetting::customer_app_home_android_4';
        }

        $settings = $store->get($setting_key);
        if (!$settings) return api_response($request, null, 404);

        $settings = json_decode($settings);
        if (empty($settings->sections)) return api_response($request, null, 404);
        if ($request->portal == 'customer-portal') {
            $this->categoryGroupPushToCategory($settings, $location);
        }

        foreach ($settings->sections as &$section) {
            if($section->item_type == 'service_group'){
                $this->addFlashOfferDataToServiceGroup($section);
            }
        }
        $settings->min_order_amount_for_emi = config('sheba.min_order_amount_for_emi');
        return api_response($request, $settings, 200, ['settings' => $settings]);
    }

    private function getPlatform(Request $request)
    {
        if ($request->has('platform')) {
            $platform = $request->platform;
        } elseif ($request->hasHeader('Platform-Name')) {
            $platform = $request->header('Platform-Name');
        } else {
            if ($request->portal == 'customer-app') {
                $platform = 'android';
            } else if ($request->portal == 'customer-portal') {
                $platform = 'web';
            } else {
                $platform = 'all';
            }
        }
        if (!isset($platform)) {
            $platform = 'android';
        }
        return $platform;
    }

    public function getCar(Request $request)
    {
        $settings = json_decode(Redis::get('car_settings'));
        return api_response($request, $settings, 200, ['settings' => $settings]);
    }

    public function getCarV3(Request $request)
    {
        $settings = json_decode(Redis::get('car_settings_v3'));
        return api_response($request, $settings, 200, ['settings' => $settings]);
    }

    public function formatWeb(array $settings, $location)
    {
        $customer_category_orders = [1, 3, 73, 101, 183, 184, 226, 186, 221, 224, 185, 225, 226, 235, 236, 333];
        $settings = collect($settings);
        $slider = $settings->where('item_type', 'slider')->first();
        $category_groups = $settings->where('item_type', 'categorygroup')->sortBy('order');
        $categories = Category::published()->where('parent_id', null)->with(['children' => function ($q) use ($location) {
            $q->select('id', 'parent_id', 'name', 'slug', 'icon_png');
            if ($location) {
                $q->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', $location);
                })->whereHas('publishedServices', function ($q) use ($location) {
                    $q->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location);
                    });
                });
            }
        }]);

        if ($location) {
            $categories->whereHas('locations', function ($q) use ($location) {
                $q->where('locations.id', $location);
            });
        }

        $categories = $categories->select('id', 'parent_id', 'name', 'thumb', 'slug', 'banner', 'icon_png')->get()->reject(function ($category) {
            return count($category->children) == 0;
        })->sortBy(function ($category) use ($customer_category_orders) {
            return array_search($category->getKey(), $customer_category_orders);
        })->values()->all();

        return ['slider' => $slider->data, 'categories' => $categories, 'category_groups' => $category_groups->values()->all()];
    }

    /**
     * @param $settings
     * @param $location
     */
    private function categoryGroupPushToCategory($settings, $location)
    {
        if (isset($settings->sections[1]) && $settings->sections[1]->item_type == 'master_categories' && Location::find($location)->city_id == 1) {
            $item_builder = (new ItemBuilder());
            $children_items = [];
            $best_deal_category_group = CategoryGroup::where('name', 'Best Deal')->first();

            $best_deal_category = $best_deal_category_group->categories()->hasLocation($location)
                ->published()->orderBy('category_group_category.order')->get();

            foreach ($best_deal_category as $child) {
                $children_items[] = $item_builder->buildCategory($child)->toArray();
            }

            $best_deal_category_group = (new ItemBuilder())->buildCategoryGroup($best_deal_category_group)->setChildren($children_items)->toArray();
            array_unshift($settings->sections[1]->data, $best_deal_category_group);
        }
    }

    /**
     * @param $section
     */
    private function addFlashOfferDataToServiceGroup(&$section)
    {
        $offer = OfferShowcase::where('target_type', "App\\Models\\ServiceGroup")
            ->where('target_id', $section->item_id)
            ->where('end_date', '>', Carbon::now())
            ->where('is_active', 1)
            ->where('is_flash', 1)
            ->orderBy('end_date')
            ->first();
        if ($offer) {
            $section->show_timer = true;
            $section->timer_end = $offer->end_date->toDateTimeString();
            $section->timer_end_timestamp = $offer->end_date->timestamp;
        }
    }
}
