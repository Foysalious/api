<?php namespace App\Http\Controllers\B2b;


use App\Models\Profile;
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


                        $member = $profile->member;
                        $businesses = $member->businesses->first();
                        $info = [
                            'token' => $this->generateToken($profile),
                            'member' => $member->id,
                            'business_id' => $businesses ? $businesses->id : null,
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

    private function generateToken(Profile $profile)
    {
        $member = $profile->member;
        $businesses = $member->businesses->first();
        return JWTAuth::fromUser($profile, [
            'member' => $member->id,
            'member_type' => $businesses ? $businesses->type : null,
            'business_id' => $businesses ? $businesses->id : null,
        ]);
    }

}