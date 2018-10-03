<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Profile;
use App\Repositories\FacebookRepository;
use App\Repositories\ProfileRepository;
use App\Sheba\SocialProfile;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GoogleController extends Controller
{
    protected $redirectTo = '/';
    private $profileRepository;
    private $facebookRepository;
    private $fbKit;

    public function __construct()
    {
        $this->profileRepository = new ProfileRepository();
        $this->facebookRepository = new FacebookRepository();
        $this->fbKit = new FacebookAccountKit();
    }

    public function login(Request $request)
    {
        try {
            $this->validate($request, ['id_token' => 'required', 'from' => "required|in:" . implode(',', constants('FROM'))]);
            if ($payload = $this->getGooglePayload($request->id_token)) {
                $profile_info = $this->extractProfileInformationFromPayload($payload);
                if ($profile = $this->profileRepository->getIfExist($profile_info['email'], 'email')) {
                    $profile = $this->profileRepository->updateIfNull($profile, $profile_info);
                    if (basename($profile->pro_pic) == 'default.jpg') {
                        $profile->pro_pic = $this->profileRepository->uploadImage($profile, $profile_info['pro_pic'], 'images/profiles/');
                        $profile->update();
                    }
                    $from = $this->profileRepository->getAvatar($request->from);
                    if ($profile->$from == null) {
                        $this->profileRepository->registerAvatar($from, $request, $profile);
                    }
                    $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
                    return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
                } else {
                    return api_response($request, null, 400, ['message' => 'Gmail account not registered! Please register']);
                }
            }
            return api_response($request, null, 403);
        } catch ( ValidationException $e ) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $this->validate($request, ['id_token' => 'required', 'kit_code' => 'required', 'from' => "required|in:" . implode(',', constants('FROM'))]);
            $payload = $this->getGooglePayload($request->id_token);
            $kit_data = $this->fbKit->authenticateKit($request->kit_code);
            if ($payload && $kit_data) {
                $social_profile = new SocialProfile(array_merge($payload, ['mobile' => formatMobile($kit_data['mobile'])]));
                $profile_info = $social_profile->getProfileInfo('google');

                $email_profile = $this->profileRepository->getIfExist($profile_info['email'], 'email');
                $mobile_profile = $this->profileRepository->getIfExist($profile_info['mobile'], 'mobile');
                if ($email_profile || $mobile_profile) {
                    $col = $email_profile ? 'Email' : 'Mobile';
                    return api_response($request, null, 400, ['message' => $col . ' already exists! Please login']);
                }
                $profile = $this->profileRepository->store($profile_info);
                $profile->pro_pic = $this->profileRepository->uploadImage($profile, $profile_info['pro_pic'], 'images/profiles/');
                $profile->update();
                $from = $this->profileRepository->getAvatar($request->from);
                if ($profile->$from == null) {
                    $this->profileRepository->registerAvatar($from, $request, $profile);
                }
                $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
                return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
            }
            return api_response($request, null, 403, ['message' => 'Authentication failed. Please try again.']);
        } catch ( ValidationException $e ) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getGooglePayload($id_token)
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_APP_CLIENT_ID')]);
        try {
            $payload = $client->verifyIdToken($id_token);
            return $payload ? $payload : null;
        } catch ( \Throwable $e ) {
            return null;
        }
    }

    private function extractProfileInformationFromPayload($payload)
    {
        return array(
            'google_id' => $payload['sub'],
            'email' => $payload['email'],
            'pro_pic' => $payload['picture'],
            'email_verified' => $payload['email_verified'] ? 1 : 0,
        );
    }
}