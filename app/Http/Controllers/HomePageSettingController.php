<?php namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HyperLocal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Cache;

class HomePageSettingController extends Controller
{
    public function index(Request $request)
    {
        try {
            /** @var \Illuminate\Contracts\Cache\Repository $store */
            $store = Cache::store('redis');
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
            $location = '';
            if ($request->has('location')) {
                $location = (int)$request->location;
            } elseif ($request->has('lat') && $request->has('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
            }
            if ($request->has('portal') && $request->has('screen')) {
                $setting_key = 'ScreenSetting::' . snake_case(camel_case($request->portal)) . '_' . $request->screen . "_" . $location;
            } else {
                $setting_key = 'ScreenSetting::customer_app_home_4';
            }
            $settings = $store->get($setting_key);
            if ($settings) {
                $settings = json_decode($settings);
                if ($request->portal == 'customer-portal') $settings = $this->formatWeb($settings, $location);
                return api_response($request, $settings, 200, ['settings' => $settings]);
            } else {
                return api_response($request, null, 404);
            }
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
        return 'publishedFor' . ucwords($for);
    }

    public function getCar(Request $request)
    {
        try {
            $settings = json_decode(Redis::get('car_settings'));
            return api_response($request, $settings, 200, ['settings' => $settings]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function formatWeb(array $settings, $location)
    {
        $customer_category_orders = [1, 3, 73, 101, 183, 184, 226, 186, 221, 224, 185, 225, 226, 235, 236];
        $settings = collect($settings);
        $slider = $settings->where('item_type', 'Slider')->first();
        $category_groups = $settings->where('item_type', 'CategoryGroup')->sortBy('order');
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
}