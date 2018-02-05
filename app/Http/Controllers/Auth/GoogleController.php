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
            if ($google_id = $this->getGoogleId($request->id_token)) {
                $profile = $this->profileRepository->ifExist($google_id, 'google_id');
                if ($profile) {
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
            $google_id = $this->getGoogleId($request->id_token);
            $kit_data = $this->fbKit->authenticateKit($request->kit_code);
            if ($google_id && $kit_data) {
                $profile = $this->profileRepository->getIfExist($google_id, 'google_id');
                if ($profile) {
                    return api_response($request, null, 400, ['message' => 'Gmail already exists! Please login']);
                }
                $profile = $this->profileRepository->getIfExist(formatMobile($kit_data['mobile']), 'mobile');
                if ($profile) {
                    return api_response($request, null, 400, ['message' => 'Mobile already exists! Please login']);
                }
                $profile = new Profile();
                DB::transaction(function () use ($kit_data, &$profile, $request) {
                    $profile = $this->profileRepository->store([
                        'email' => $request->email, 'mobile' => formatMobile($kit_data['mobile']), 'name' => trim($request->name), 'mobile_verified' => 1, 'email_verified' => 1
                    ]);
                    $profile->pro_pic = $this->profileRepository->uploadImage($profile, $request->picture, 'images/profiles/');
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

    private function getGoogleId($id_token)
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_APP_CLIENT_ID')]);
        try {
            $payload = $client->verifyIdToken($id_token);
            if ($payload) {
                // If request specified a G Suite domain:
                return $payload['sub'];
            } else {
                return null;
            }
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