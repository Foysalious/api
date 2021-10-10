<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Profile;
use App\Repositories\FacebookRepository;
use App\Repositories\ProfileRepository;
use App\Sheba\SocialProfile;
use Google_Client;
use Illuminate\Http\Request;
use Sheba\OAuth2\AccountServer;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;

class GoogleController extends Controller
{
    protected $redirectTo = '/';
    /** @var ProfileRepository  */
    private $profileRepository;
    /** @var FacebookRepository  */
    private $facebookRepository;
    /** @var FacebookAccountKit  */
    private $fbKit;
    /** @var AccountServer $accounts */
    private $accounts;

    public function __construct(ProfileRepository $profile_repo, FacebookRepository $fb_repo, FacebookAccountKit $fb_kit, AccountServer $accounts)
    {
        $this->profileRepository = $profile_repo;
        $this->facebookRepository = $fb_repo;
        $this->fbKit = $fb_kit;
        $this->accounts = $accounts;
    }

    public function login(Request $request)
    {
        $this->validate($request, ['id_token' => 'required', 'from' => "required|in:" . implode(',', constants('FROM'))]);
        $payload = $this->getGooglePayload($request->id_token);
        if (!$payload) return api_response($request, null, 403);

        $profile_info = $this->extractProfileInformationFromPayload($payload);
        $profile = $this->profileRepository->getIfExist($profile_info['email'], 'email');
        if (!$profile) return api_response($request, null, 400, ['message' => 'Gmail account not registered! Please register']);

        $profile = $this->profileRepository->updateIfNull($profile, $profile_info);
        if ($profile_info['pro_pic'] && basename($profile->pro_pic) == 'default.jpg') {
            $profile->pro_pic = $this->profileRepository->uploadImage($profile, $profile_info['pro_pic'], 'images/profiles/');
            $profile->update();
        }
        $from = $this->profileRepository->getAvatar($request->from);
        if ($profile->$from == null) $this->profileRepository->registerAvatar($from, $request, $profile);
        $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
        return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
    }

    public function register(Request $request)
    {
        $this->validate($request, ['id_token' => 'required', 'kit_code' => 'required', 'from' => "required|in:" . implode(',', constants('FROM'))]);
        $payload = $this->getGooglePayload($request->id_token);
        $kit_data = $this->resolveAccountKit($request->kit_code);
        if (!($payload && $kit_data)) return api_response($request, null, 403, ['message' => 'Authentication failed. Please try again.']);

        $social_profile = new SocialProfile(array_merge($payload, ['mobile' => formatMobile($kit_data['mobile'])]));
        $profile_info = $social_profile->getProfileInfo('google');

        $email_profile = $this->profileRepository->getIfExist($profile_info['email'], 'email');
        $mobile_profile = $this->profileRepository->getIfExist($profile_info['mobile'], 'mobile');
        if ($email_profile || $mobile_profile) {
            $col = $email_profile ? 'Email' : 'Mobile';
            return api_response($request, null, 400, ['message' => $col . ' already exists! Please login']);
        }
        if($request->hasHeader('portal-name')) array_add($profile_info, 'portal_name', $request->header('portal-name'));
        $profile = $this->profileRepository->store($profile_info);
        $profile->pro_pic = $this->profileRepository->uploadImage($profile, $profile_info['pro_pic'], 'images/profiles/');
        $profile->update();
        $from = $this->profileRepository->getAvatar($request->from);
        $is_new = 0;
        if ($profile->$from == null) {
            $is_new = 1;
            $this->profileRepository->registerAvatar($from, $request, $profile);
        }
        $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
        $info['is_new'] = $is_new;
        return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
    }

    private function resolveAccountKit($code)
    {
        $version = (int)\request()->header('Version-Code');
        $portal_name = \request()->header('portal-name');
        $platform_name = \request()->header('Platform-Name');
        if ($portal_name == 'customer-portal' || ($version > 30211 && $portal_name == 'customer-app') || ($version > 12003 && $portal_name == 'bondhu-app') || ($version > 2145 && $portal_name == 'resource-app') ||
            ($version > 126 && $portal_name == 'customer-app' && $platform_name == 'ios')) {
            $access_token_request = new AccessTokenRequest();
            $access_token_request->setAuthorizationCode($code);
            $account_kit = app(ShebaAccountKit::class);
            $kit = [];
            $mobile = $account_kit->getMobile($access_token_request);
            if (!$mobile) return null;
            $kit['mobile'] = $mobile;
            return $kit;
        }
        return $this->fbKit->authenticateKit($code);
    }

    private function getGooglePayload($id_token)
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_APP_CLIENT_ID')]);
        try {
            $payload = $client->verifyIdToken($id_token);
            return $payload ? $payload : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function extractProfileInformationFromPayload($payload)
    {
        return [
            'google_id' => $payload['sub'],
            'email' => $payload['email'],
            'pro_pic' => isset($payload['picture']) ? $payload['picture'] : null,
            'email_verified' => $payload['email_verified'] ? 1 : 0,
        ];
    }
}
