<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Validation\ValidationException;
use Sheba\Repositories\Business\MemberRepository;
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
    private $memberRepository;

    public function __construct(ProfileRepository $profile_repository, MemberRepository $member_repository)
    {
        $this->profileRepository = $profile_repository;
        $this->memberRepository = $member_repository;
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
                if (!Hash::check($request->input('password'), $profile->password)) {
                    return api_response($request, null, 401, ["message" => 'Credential mismatch']);
                }
                $member = $profile->member;
                $businesses = $member ? $member->businesses->first() : null;

                if (!$member) {
                    $member = $this->memberRepository->create([
                        'profile_id' => $profile->id,
                        'remember_token' => str_random(255),
                    ]);
                }
                $info = [
                    'token' => $this->generateToken($profile),
                    'member_id' => $member->id,
                    'business_id' => $businesses ? $businesses->id : null,
                    'is_super' => $member->businessMember ? $member->businessMember->is_super : null,
                ];
                return api_response($request, $info, 200, ['info' => $info]);
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
        $businesses = $member->businesses->first() ? $member->businesses->first() : null;
        return JWTAuth::fromUser($profile, [
            'member_id' => $member->id,
            'member_type' => count($member->businessMember) > 0 ? $member->businessMember->first()->type : null,
            'business_id' => $businesses ? $businesses->id : null,
        ]);
    }

    public function generateDummyToken()
    {
        $business_member = BusinessMember::where([
            ['business_id', 1],
//            ['is_super', 1],
//            ['id',4],
            ['member_id', 13]
        ])->first();
        $member = $business_member->member;
        return JWTAuth::fromUser($business_member->member->profile, [
            'member_id' => $member->id,
            'member_type' => count($member->businessMember) > 0 ? $member->businessMember->first()->type : null,
            'business_id' => $business_member->business_id,
        ]);
    }

}