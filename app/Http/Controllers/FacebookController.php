<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Repositories\FacebookRepository;
use App\Repositories\ProfileRepository;
use App\Sheba\FacebookProfile;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Portals\Portals;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;
use Throwable;
use Validator;
use DB;

class FacebookController extends Controller
{
    private $fbKit;
    private $profileRepository;
    private $facebookRepository;


    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
        $this->facebookRepository = new FacebookRepository();
    }

    public function login(Request $request)
    {
        try {
            $from = implode(',', constants('FROM'));
            $this->validate($request, ['access_token' => 'required', 'from' => "required|in:$from"]);
            if ($fb_profile_info = $this->getFacebookProfileInfo($request->access_token)) {
                $fb_profile_info = (new FacebookProfile($fb_profile_info))->getProfileInformation();
                $profile = $this->profileRepository->getIfExist($fb_profile_info['fb_id'], 'fb_id');
                if ($profile) {
                    $from = $this->profileRepository->getAvatar($request->from);
                    if ($profile->$from == null) {
                        $this->profileRepository->registerAvatar($from, $request, $profile);
                    }
                    $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
                    return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
                } else {
                    return api_response($request, null, 400, ['message' => 'Facebook account not registered! Please register']);
                }
            }
            return api_response($request, null, 403);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $from = implode(',', constants('FROM'));
            $this->validate($request, ['access_token' => 'required', 'kit_code' => 'required', 'from' => "required|in:$from"]);
            $fb_profile_info = $this->getFacebookProfileInfo($request->access_token);
            $kit_data = $this->resolveAccountKit($request->kit_code);
            if ($fb_profile_info && $kit_data) {
                $from = $this->profileRepository->getAvatar($request->from);
                $fb_profile = new FacebookProfile($fb_profile_info);
                $fb_profile_info = $fb_profile->getProfileInformation();
                $profile = $this->profileRepository->getIfExist($fb_profile_info['fb_id'], 'fb_id');
                if (!$profile) {
                    $profile = $this->profileRepository->getIfExist(formatMobile($kit_data['mobile']), 'mobile');
                    if (!$profile) {
                        $profile = $this->profileRepository->getIfExist($fb_profile_info['email'], 'email');
                        if (!$profile) {
                            DB::transaction(function () use ($fb_profile_info, $kit_data, &$profile) {
                                $profile = $this->profileRepository->store(array_merge($fb_profile_info, ['mobile' => formatMobile($kit_data['mobile']), 'mobile_verified' => 1]));
                                $profile->pro_pic = $this->profileRepository->uploadImage($profile, $fb_profile_info['pro_pic'], 'images/profiles/');
                                $profile->update();
                            });
                        } else {
                            return api_response($request, null, 400, ['message' => 'Email already exists! Please login']);
                        }
                    } else {
                        return api_response($request, null, 400, ['message' => 'Mobile already exists! Please login']);
                    }
                } else {
                    return api_response($request, null, 400, ['message' => 'Facebook already exists! Please login']);
                }
                if ($profile->$from == null) {
                    $this->profileRepository->registerAvatar($from, $request, $profile);
                }
                $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
                return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
            }
            return api_response($request, null, 403);
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

    private function resolveAccountKit($code)
    {
        if (!$this->isUsingShebaAccountKit()) return $this->fbKit->authenticateKit($code);

        $access_token_request = new AccessTokenRequest();
        $access_token_request->setAuthorizationCode($code);
        $account_kit = app(ShebaAccountKit::class);
        $mobile = $account_kit->getMobile($access_token_request);
        if (!$mobile) return null;
        return ['mobile' => $mobile];
    }

    /**
     * @return bool
     */
    private function isUsingShebaAccountKit()
    {
        $version = convertSemverToInt(\request()->header('Version-Code'));
        $portal_name = \request()->header('portal-name');
        $platform_name = \request()->header('Platform-Name');

        return $portal_name == 'customer-portal' ||
            ($version > 30211 && $portal_name == 'customer-app') ||
            ($version > 12003 && $portal_name == 'bondhu-app') ||
            ($version > 2145 && $portal_name == 'resource-app') ||
            ($version > 126 && $portal_name == 'customer-app' && $platform_name == 'ios') ||
            $portal_name == Portals::BUSINESS_WEB;
    }

    private function getFacebookProfileInfo($token)
    {
        try {
            $client = new Client();
            $res = $client->request('GET', 'https://graph.facebook.com/me?fields=id,name,email,gender,picture.height(400).width(400)&access_token=' . $token);

            return json_decode($res->getBody(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    public function continueWithKit(Request $request, AuthUser $authUser, ProfileRepositoryInterface $profileRepository)
    {
        $this->validate($request, [
            'code' => "required",
            'from' => 'required|string|in:' . implode(',', constants('FROM'))
        ]);
        $code_data = $this->resolveAccountKit($request->code);
        if (!$code_data) return api_response($request, null, 401);
        $code_data['mobile'] = formatMobile($code_data['mobile']);
        $profile = $profileRepository->findByMobile($code_data['mobile'])->first();
        if ($profile && $profile->isBlackListed()) return api_response($request, null, 403, ['message' => "Your account is blocked."]);
        $from = $this->profileRepository->getAvatar($request->from);
        if ($profile == false) {
            array_add($request, 'mobile', $code_data['mobile']);
            $profile = $this->profileRepository->registerMobile($request->all());
            $this->profileRepository->registerAvatarByKit($from, $profile);
        }
        if ($profile->$from == null) {
            $this->profileRepository->registerAvatarByKit($from, $profile);
            $profile = Profile::find($profile->id);
        }
        $info = $this->profileRepository->getProfileInfo($from, $profile, $request);
        if (!$info) return api_response($request, null, 404);
        $info['jwt']['token'] = $authUser->setProfile($profile)->generateToken();
        return api_response($request, $info, 200, ['info' => $info]);
    }

    public function continueWithFacebook(Request $request)
    {
        try {
            if ($msg = $this->_validateFacebookRequest($request)) {
                return response()->json(['code' => 500, 'msg' => $msg]);
            }
            //validate access token
            if ($fb_profile_image_url = $this->facebookRepository->verifyAccessToken($request->access_token, $request->fb_id)) {
                $avatar = $this->profileRepository->getAvatar($request->from);
                $profile = $this->profileRepository->ifExist($request->input('fb_id'), 'fb_id');
                if ($profile == false) {
                    $email_profile = $this->profileRepository->ifExist($request->fb_email, 'email');
                    if ($email_profile == false) {
                        $profile = $this->profileRepository->registerFacebook($request->all());
                        $profile->pro_pic = $this->profileRepository->uploadImage($profile, $fb_profile_image_url, 'images/profiles/');
                        $profile->update();
                    } else {
                        $profile = $this->profileRepository->integrateFacebook($email_profile, $request);
                    }
                }
                if ($profile->$avatar == null) {
                    $this->profileRepository->registerAvatarByFacebook($avatar, $request, $profile);
                    $profile = Profile::find($profile->id);
                }
                $info = $this->profileRepository->getProfileInfo($avatar, $profile, $request);
                if ($info != false) {
                    return response()->json(['code' => 200, 'info' => $info]);
                }
            }
            return response()->json(['code' => 404, 'msg' => 'Not found!']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function _validateFacebookRequest($request)
    {
        $from = implode(',', constants('FROM'));
        $validator = Validator::make($request->all(), [
            'from' => "required|in:$from",
            'access_token' => "required"
        ], ['in' => 'from value is invalid!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
