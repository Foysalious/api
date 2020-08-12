<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Sheba\Business\CoWorker\Statuses;
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
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var MemberRepository $memberRepository */
    private $memberRepository;

    /**
     * LoginController constructor.
     * @param ProfileRepository $profile_repository
     * @param MemberRepository $member_repository
     */
    public function __construct(ProfileRepository $profile_repository, MemberRepository $member_repository)
    {
        $this->profileRepository = $profile_repository;
        $this->memberRepository = $member_repository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, ['email' => 'required', 'password' => 'required']);

        $profile = $this->profileRepository->ifExist($request->email, 'email');
        if (!$profile) return api_response($request, null, 404);
        if (!Hash::check($request->input('password'), $profile->password)) {
            return api_response($request, null, 401, ["message" => 'Credential mismatch']);
        }
        /** @var Member $member */
        $member = $profile->member;
        $businesses = ($member && $member->businessMember) ? $member->businessMember->business : null;

        if (!$member) {
            $member = $this->memberRepository->create(['profile_id' => $profile->id, 'remember_token' => str_random(255)]);
        }

        $info = [
            'token' => $this->generateToken($member),
            'member_id' => $member->id,
            'business_id' => $businesses ? $businesses->id : null,
            'is_super' => $member->businessMember ? $member->businessMember->is_super : null
        ];

        return api_response($request, $info, 200, ['info' => $info]);
    }

    /**
     * @param Member $member
     * @return mixed
     */
    private function generateToken(Member $member)
    {
        /** @var Profile $profile */
        $profile = $member->profile;
        $businesses = $member->businessMember ? $member->businessMember->business : null;

        return JWTAuth::fromUser($profile, [
            'member_id'     => $member->id,
            'member_type'   => $member->businessMember ? $member->businessMember->type : null,
            'business_id'   => $businesses ? $businesses->id : null
        ]);
    }

    public function generateDummyToken()
    {
        $business_member = BusinessMember::where([
            ['business_id', 11],
            ['member_id', 17]
        ])->first();
        $member = $business_member->member;
        return JWTAuth::fromUser($business_member->member->profile, [
            'member_id' => $member->id,
            'member_type' => count($member->businessMember) > 0 ? $member->businessMember->first()->type : null,
            'business_id' => $business_member->business_id,
        ]);
    }
}
