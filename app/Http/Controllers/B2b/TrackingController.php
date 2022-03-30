<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use Illuminate\Http\Request;
use Sheba\Business\LiveTracking\Updater;
use Sheba\ModificationFields;

class TrackingController extends Controller
{
    use ModificationFields;

    public function settingsAction(Request $request, Updater $updater)
    {
        $this->validate($request, [
            'is_enable' => 'required|digits_between:0,1'
        ]);
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $this->setModifier($business_member);
        $updater->setBusiness($business)->setIsEnable($request->is_enable)->update();
        return api_response($request, null, 200);
    }

}
