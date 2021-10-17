<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\AttendanceReconciliation\Creator;
use App\Sheba\Business\AttendanceReconciliation\Requester;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class AttendanceReconciliationController extends Controller
{
    use ModificationFields;

    public function create(Request $request, Requester $requester, Creator $creator)
    {
        $this->validate($request, [
            'checkin' => 'sometimes|required|date_format:H:i:s',
            'checkout' => 'sometimes|required|date_format:H:i:s',
            'business_member_id' => 'required'
        ]);
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $requester->setBusiness($business)
                    ->setBusinessMember($request->business_member_id)
                    ->setCheckinTime($request->checkin)
                    ->setCheckoutTime($request->checkout);
        if ($requester->getError()) return api_response($request, null, 404);
        $creator->setRequester($requester)->create();

    }

}