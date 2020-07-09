<?php namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Sheba\Apple\Authentication;
use Sheba\Customer\Creator;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;

class AppleController extends Controller
{
    public function register(Request $request, Authentication $authentication, ProfileRepository $profileRepository, AccessTokenRequest $accessTokenRequest, Creator $creator, ShebaAccountKit $accountKit)
    {
        $this->validate($request, ['authorization_code' => 'required', 'kit_code' => 'required', 'from' => "required|in:" . implode(',', constants('FROM'))]);
        $accessTokenRequest->setAuthorizationCode($request->kit_code);
        $mobile = $accountKit->getMobile($accessTokenRequest);
        $user_response = $authentication->getUser($request->authorization_code);
        if ($user_response->hasError() || !$mobile) return api_response($request, null, 500);
        $email_profile = $profileRepository->getIfExist($user_response->getEmail(), 'email');
        $mobile_profile = $profileRepository->getIfExist($mobile, 'mobile');
        if ($email_profile || $mobile_profile) return api_response($request, null, 400, ['message' => $email_profile ? 'Email' : 'Mobile' . ' already exists! Please login']);
        $profile = $profileRepository->store(['mobile' => $mobile, 'email_verified' => $user_response->getEmailVerified(), 'email' => $user_response->getEmail()]);
        $creator->setMobile($mobile)->create();
        $info = $profileRepository->getProfileInfo($request->from, Profile::find($profile->id), $request);
        return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
    }

    public function login(Request $request, Authentication $authentication, ProfileRepository $profileRepository)
    {

        $this->validate($request, ['authorization_code' => 'required', 'from' => "required|in:" . implode(',', constants('FROM'))]);
        $user_response = $authentication->getUser($request->authorization_code);
        if ($user_response->hasError()) return api_response($request, null, 500);
        $profile = $profileRepository->getIfExist($user_response->getEmail(), 'email');
        if (!$profile) return api_response($request, null, 400, ['message' => 'Account is not registered. Please register']);
        $info = $profileRepository->getProfileInfo($request->from, $profile, $request);
        return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
    }
}