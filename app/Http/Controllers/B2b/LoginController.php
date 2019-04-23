<?php namespace App\Http\Controllers\B2b;


use App\Repositories\ProfileRepository;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTFactory;
use Validator;
use JWTAuth;
use Session;
use Hash;

class LoginController extends Controller
{
    private $profileRepository;

    public function __construct(ProfileRepository $profile_repository)
    {
        $this->profileRepository = $profile_repository;
    }

    public function login(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'password' => 'required'
            ]);
            $profile = $this->profileRepository->ifExist($request->email, 'email');

            if ($profile) {
                $member = $profile->member;
                if ($member) {
                    if (Hash::check($request->input('password'), $profile->password)) {
                        $token = JWTAuth::fromUser($profile);
                        $info = [
                            'token' => $token,
                            'remember_token' => $profile->remember_token,
                            'member' => $member->id,
                            'member_img' => $profile->pro_pic
                        ];
                        return api_response($request, $info, 200, ['info' => $info]);
                    }
                }
                return api_response($request, null, 404, ["message" => 'Member not found.']);
            } else {
                return api_response($request, null, 404);
            }

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
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
        $from = implode(',', constants('FROM'));
        $validator = Validator::make($request->all(), [

        ], ['in' => 'from value is invalid!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

}