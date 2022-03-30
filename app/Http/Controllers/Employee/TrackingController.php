<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\TrackingLocation;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Http\Request;
use Sheba\ModificationFields;

class TrackingController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    public function insertLocation(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $business = $this->getBusiness($request);
        $manager_member = $this->getMember($request);
        $this->setModifier($manager_member);

        $locations = $request->locations;
        $data = [];
        foreach ($locations as $location) {
            $geo = json_encode([$location['lat'], $location['lng']]);
            $data[] = [
                'business_id' => $business->id,
                'business_member_id' => $business_member->id,
                'geo' => $geo,
                'log' => $location['log'],
                'dateTime' => $location['datetime'],
            ];
        }
        TrackingLocation::insert($data);

        $req = $request->except('access_token', 'auth_user', 'auth_info', 'manager_member', 'business', 'business_member', 'token', 'profile');

        return api_response($request, null, 200, ['data' => $req]);
    }
}