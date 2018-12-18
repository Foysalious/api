<?php

namespace App\Http\Controllers;

use App\Models\HyperLocal;
use Illuminate\Http\Request;
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
            ]);
            $setting_key = null;
            $location = null;
            if ($request->has('location')) {
                $location = $request->location;
            } elseif ($request->has('lat') && $request->has('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
            }
            if ($request->has('portal') && $request->has('screen')) {
                $setting_key = 'ScreenSetting::' . snake_case(camel_case($request->portal)) . '_' . $request->screen . "_";
            } else {
                $setting_key = 'ScreenSetting::customer_app_home_';
            }
            $setting_key .= $location ? $location : 4;
            $settings = $store->get($setting_key);
            return $settings ? api_response($request, json_decode($settings), 200, ['settings' => json_decode($settings)]) : api_response($request, null, 404);
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
            $settings = json_decode(\Illuminate\Support\Facades\Redis::get('car_settings'));
            return api_response($request, $settings, 200, ['settings' => $settings]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}