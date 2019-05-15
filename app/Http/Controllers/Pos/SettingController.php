<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PartnerPosSetting;
use Illuminate\Http\Request;
use Sheba\Pos\Setting\Creator;

class SettingController extends Controller
{
    public function getSettings(Request $request, Creator $creator)
    {
        try {
            $partner = $request->partner;
            $settings = PartnerPosSetting::byPartner($partner->id)->first();
            if (!$settings) {
                $data = ['partner_id' => $partner->id,];
                $creator->setData($data)->create();
                $settings = PartnerPosSetting::byPartner($partner->id)->first();
            }
            removeRelationsAndFields($settings);
            return api_response($request, $settings, 200, ['settings' => $settings]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}