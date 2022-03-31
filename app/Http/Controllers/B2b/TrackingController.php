<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use Illuminate\Http\Request;
use Sheba\Business\LiveTracking\ChangeLogs\Creator as ChangeLogsCreator;
use Sheba\Business\LiveTracking\Updater as SettingsUpdater;
use Sheba\ModificationFields;

class TrackingController extends Controller
{
    use ModificationFields;

    public function settingsAction(Request $request, SettingsUpdater $updater, ChangeLogsCreator $change_logs_creator)
    {
        $this->validate($request, [
            'is_enable' => 'required|digits_between:0,1'
        ]);
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $this->setModifier($request->manager_member);
        $live_tracking_setting = $updater->setBusiness($business)->setIsEnable($request->is_enable)->update();
        if ($live_tracking_setting) $change_logs_creator->setLiveTrackingSetting($live_tracking_setting)->setIsEnable($request->is_enable)->create();
        return api_response($request, null, 200);
    }

}
