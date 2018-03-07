<?php

namespace App\Http\Controllers;


use App\Models\HomepageSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Redis;

class HomePageSettingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'required|string|in:app,web,app_json'
            ]);
            if ($request->for == 'app_json') {
                $settings = json_decode(Redis::get('app_settings'));
                return api_response($request, $settings, 200, ['settings' => $settings]);
            }
            $for = $this->getPublishedFor($request->for);
            $settings = HomepageSetting::$for()->select('id', 'order', 'item_type', 'item_id', 'updated_at')->orderBy('order')->get();
            foreach ($settings as $setting) {
                $setting->item_type = str_replace('App\Models\\', "", $setting->item_type);
                $setting['updated_at_timestamp'] = $setting->updated_at->timestamp;
            }
            return count($settings) > 0 ? api_response($request, $settings, 200, ['settings' => $settings]) : api_response($request, $settings, 404);
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