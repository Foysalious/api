<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Affiliate\Creator;
use Sheba\ModificationFields;

class AffiliateRegistrationController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @param Creator $creator
     * @param ProfileRepository $profile_repo
     * @return JsonResponse
     */
    public function registerByProfile(Request $request, Creator $creator, ProfileRepository $profile_repo)
    {
        $this->validate($request, []);
        $profile = $request->profile;
        if ($profile->affiliate) return api_response($request, null, 409);

        $data = $this->getUpdatableData($request);
        $profile_repo->update($profile, $data);
        $geoLocation = $this->getGeoLocation($request);
        $creator->setGeolocation($geoLocation)->setProfile($profile)->create();
        $video_link = config('constants.AFFILIATE_VIDEO_LINK');
        return api_response($request, null,200,['video_link' => $video_link]);
    }

    /**
     * @param $request
     * @return array
     */
    private function getUpdatableData($request)
    {
        $data = [];
        if($request->has('name')) $data['name'] = $request->name;
        if($request->has('address')) $data['address'] = $request->address;
        if($request->has('gender')) $data['gender'] = $request->gender;
        return $data;
    }

    /**
     * @param $request
     * @return string|null
     */
    private function getGeoLocation($request)
    {
        $location = null;
        if ($request->has('lat') && ($request->has('lon') || $request->has('lng'))) {
            $lat = $request->lat;
            $lon = $request->has('lon') ? $request->lon : $request->lng;
            $location = "{'lat':$lat,'lng':$lon}";
        }
        return $location;
    }
}
