<?php

namespace App\Http\Controllers;


use App\Models\HomepageSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HomePageSettingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'sometimes|required|string|in:app,web'
            ]);
            $for = $this->getPublishedFor($request->for);
            $settings = HomepageSetting::$for()->select('id', 'order', 'settings_type', 'settings_type_id', 'updated_at')->orderBy('order')->get();
            foreach ($settings as $setting) {
                $setting->settings_type = str_replace('App\Models\\', "", $setting->settings_type);
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