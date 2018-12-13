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
            $portals = config('sheba.portals');
            $screens = config('sheba.screen');
            $this->validate($request, [
                'for' => 'string|in:app,web,app_json,app_json_revised',
                'portal_name' => 'in:'.implode(',',$portals),
                'screen' => 'in:'.implode(',', $screens),
            ]);

            $setting_key = '';
            if($request->has('portal_name' && $request->has('screen') && $request->has('location'))) {
                $setting_key = 'settings_'.$request->portal_name.'_'.$request->screen."_".$request->location;
            }

            if ($request->for == 'app_json' || $request->for == 'app_json_revised') {
                $settings = json_decode(Redis::get('settings_customer-app_home_4'));
            } else {
                if($setting_key!=='')
                    $settings = json_decode(Redis::get($setting_key));
                else
                    $settings = json_decode(Redis::get('settings_customer-app_home_4'));
            }

            if(!is_null($settings))
                return api_response($request, $settings, 200, ['settings' => $settings]);
            else
                return api_response($request, 'Not Found', 404, ['message' => 'Not Found']);



// This are previous codes, kept for QA Purposes

            //                    $settings = json_decode(Redis::get('app_settings'));
//                    return api_response($request, $settings, 200, ['settings' => $settings]);

            //                        $for = $this->getPublishedFor($request->for);
//                        $settings = HomepageSetting::$for()->select('id', 'order', 'item_type', 'item_id', 'updated_at')->orderBy('order')->get();
//                        foreach ($settings as $setting) {
//                            $setting->item_type = str_replace('App\Models\\', "", $setting->item_type);
//                            $setting['updated_at_timestamp'] = $setting->updated_at->timestamp;
//                        }
//                        return count($settings) > 0 ? api_response($request, $settings, 200, ['settings' => $settings]) : api_response($request, $settings, 404);

//                else if ($request->for == 'app_json_revised') {
//                    $settings = json_decode(Redis::get('app_settings_revised'));
//                    return api_response($request, $settings, 200, ['settings' => $settings]);
//                }


        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
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