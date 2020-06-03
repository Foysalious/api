<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Profile;
use App\Repositories\CustomerRepository;
use App\Repositories\ProfileRepository;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use JWTFactory;
use Session;
use Validator;

class LoginController extends Controller
{
    private $fbKit;
    private $customer;
    private $profileRepository;

    public function __construct()
    {
        $this->fbKit             = new FacebookAccountKit();
        $this->customer          = new CustomerRepository();
        $this->profileRepository = new ProfileRepository();
    }

    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email'    => 'required',
                'password' => 'required',
                'from'     => 'required|string|in:' . implode(',', constants('FROM'))
            ]);
            /** @var Profile $profile */
            $profile = $this->profileRepository->ifExist($request->email, 'email');
            if ($profile == false) {
                $profile = $this->profileRepository->ifExist(formatMobile($request->email), 'mobile');
            }
            if ($profile != false) {
                if (Hash::check($request->input('password'), $profile->password)) {
                    $info = $this->profileRepository->getProfileInfo($this->profileRepository->getAvatar($request->from), $profile, $request);
                    if ($info != null) {
                        return api_response($request, $info, 200, ['info' => $info]);
                    }
                    return api_response($request, null, 400, ['message' => 'Profile info not found']);
                }
                return api_response($request, null, 400, ['message' => 'Password does\'t match']);
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context([
                'request' => $request->all(),
                'message' => $message
            ]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function formatMobile($mobile)
    {
        // mobile starts with '+88'
        if (preg_match("/^(\+88)/", $mobile)) {
            return $mobile;
        } // when mobile starts with '88' replace it with '+880'
        elseif (preg_match("/^(88)/", $mobile)) {
            return preg_replace('/^88/', '+88', $mobile);
        } // real mobile no add '+880' at the start
        else {
            return '+88' . $mobile;
        }
    }

    private function _validateLoginRequest($request)
    {
        $from      = implode(',', constants('FROM'));
        $validator = Validator::make($request->all(), [], ['in' => 'from value is invalid!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

}
