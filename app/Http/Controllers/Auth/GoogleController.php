<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Profile;
use App\Repositories\FacebookRepository;
use App\Repositories\ProfileRepository;
use Google_Client;
use Illuminate\Http\Request;
use Auth;
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
            $from = implode(',', constants('FROM'));
            $this->validate($request, ['id_token' => 'required', 'from' => "required|in:$from"]);
            if ($payload = $this->getGooglePayload($request->id_token)) {
                $profile = $this->profileRepository->ifExist($payload['email'], 'email');
                if ($profile) {
                    if (basename($profile->pro_pic) == 'default.jpg') {
                        $profile->pro_pic = $this->profileRepository->uploadImage($profile, $payload['picture'], 'images/profiles/');
                    }
                    if ($profile->google_id == null) {
                        $profile->google_id = $payload['sub'];
                    }
                    $profile->email_verified = 1;
                    $profile->update();
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
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $from = implode(',', constants('FROM'));
            $this->validate($request, ['id_token' => 'required', 'email' => 'required|email', 'kit_code' => 'required', 'from' => "required|in:$from"]);
            $payload = $this->getGooglePayload($request->id_token);
            $kit_data = $this->fbKit->authenticateKit($request->kit_code);
            if ($payload && $kit_data) {
                $profile = $this->profileRepository->getIfExist($payload['email'], 'email');
                if ($profile) {
                    return api_response($request, null, 400, ['message' => 'Gmail already exists! Please login']);
                }
                $profile = $this->profileRepository->getIfExist(formatMobile($kit_data['mobile']), 'mobile');
                if ($profile) {
                    return api_response($request, null, 400, ['message' => 'Mobile already exists! Please login']);
                }
                $profile = new Profile();
                DB::transaction(function () use ($kit_data, &$profile, $payload) {
                    $profile = $this->profileRepository->store([
                        'email' => $payload['email'], 'mobile' => formatMobile($kit_data['mobile']), 'name' => trim($payload['name']), 'mobile_verified' => 1, 'email_verified' => 1, 'google_id' => $payload['sub']
                    ]);
                    $profile->pro_pic = $this->profileRepository->uploadImage($profile, $payload['picture'], 'images/profiles/');
                    $profile->update();
                });
                if ($profile->$from == null) {
                    $this->profileRepository->registerAvatar($from, $request, $profile);
                }
                $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
                return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
            }
            return api_response($request, null, 403);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
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

    public function create(array $data)
    {
        $profile = new Profile();
        foreach ($data as $key => $value) {
            $profile->$key = $value;
        }
        $profile->remember_token = str_random(255);
        $profile->mobile_verified = 1;
        $profile->email_verified = 1;
        $profile->save();
        return $profile;
    }
}