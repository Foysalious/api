<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;

use App\Models\Affiliate;
use App\Repositories\ProfileRepository;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Sheba\Affiliate\Creator;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
use Sheba\Sms\Sms;
use DB;
use Throwable;

class AffiliateRegistrationController extends Controller
{
    use ModificationFields;

    /** @var FacebookAccountKit $fbKit */
    private $fbKit;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var Sms */
    private $sms;
    /** @var EntryRepository $entryRepo */
    private $entryRepo;
    /** @var PartnerRepositoryInterface $partnerRepo */
    private $partnerRepo;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
        $this->sms = new Sms();
    }


    /**
     * @param Request $request
     * @param Creator $creator
     * @param ProfileRepository $profile_repo
     * @return JsonResponse
     */
    public function registerByProfile(Request $request, Creator $creator, ProfileRepository $profile_repo)
    {
        try {
            $this->validate($request, []);
            $profile = $request->profile;
            if ($profile->affiliate)
                return api_response($request, null, 409);

            $data = $this->getUpdatableData($request);
            $profile_repo->update($profile, $data);
            $geoLocation = $this->getGeoLocation($request);
            $creator->setGeolocation($geoLocation)->setProfile($profile)->create();
            $video_link = config('constants.AFFILIATE_VIDEO_LINK');
            return api_response($request, null,200,['video_link' => $video_link]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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
        if($request->has('lat') && $request->has('lon')){
            $lat = $request->lat;
            $lon = $request->lon;
            $location = "{'lat':$lat,'lng':$lon}";
        }
        return $location;
    }
}
