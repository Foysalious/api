<?php namespace App\Http\Controllers\B2b;

use App\Jobs\Business\SendMailVerificationCodeEmail;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Sheba\Business\CoWorker\Statuses;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\OAuth2\AuthUser;
use Sheba\OAuth2\SomethingWrongWithToken;
use Sheba\Repositories\Business\MemberRepository;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
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
    /** @var AccountServer */
    private $accounts;
    /** @var ProfileRepositoryInterface $profileRepository */
    private $profileRepository;

    /**
     * LoginController constructor.
     * @param AccountServer $accounts
     * @param ProfileRepositoryInterface $profile_repository
     */
    public function __construct(AccountServer $accounts, ProfileRepositoryInterface $profile_repository)
    {
        $this->accounts = $accounts;
        $this->profileRepository = $profile_repository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     * @throws SomethingWrongWithToken
     */
    public function login(Request $request)
    {
        $this->validate($request, ['email' => 'required', 'password' => 'required']);
        $profile = $this->profileRepository->checkExistingEmail($request->email);
        $member = $profile->member;
        $business_members = BusinessMember::where('member_id', $member->id)->get();
        if ($business_members->isEmpty()) return api_response($request, null, 403, ['message' => 'Please register first']);
        if (!$business_members->isEmpty()) {
            $business_members = $business_members->reject(function ($business_member) {
                return $business_member->status == Statuses::INACTIVE;
            });

            if (!$business_members->count()) return api_response($request, null, 420, ['message' => 'You account deactivated from this company']);
        }
        $token = $this->accounts->createAvatarAndGetTokenByEmailAndPassword('member', $request->email, $request->password);
        $auth_user = AuthUser::createFromToken($token);
        $info = [
            'token' => $token,
            'email_verified' => $auth_user->isEmailVerified(),
            'member_id' => $auth_user->getMemberId(),
            'business_id' => $auth_user->getMemberAssociatedBusinessId(),
            'is_super' => $auth_user->isMemberSuper()
        ];
        if (!$auth_user->isEmailVerified()) {
            $this->sendVerificationCode($auth_user->getProfileId());
        }
        return api_response($request, $info, 200, ['info' => $info]);
    }

    private function sendVerificationCode($profile_id)
    {
        $this->dispatch((new SendMailVerificationCodeEmail($profile_id)));
    }

    /**
     * @return string
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function generateDummyToken()
    {
        $business_member = BusinessMember::where([
            ['business_id', 11],
            ['member_id', 17]
        ])->first();

        return $this->accounts->getTokenByAvatar('member', $business_member->member);
    }
}
