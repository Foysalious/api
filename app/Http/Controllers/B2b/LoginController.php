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

    /**
     * LoginController constructor.
     * @param AccountServer $accounts
     */
    public function __construct(AccountServer $accounts)
    {
        $this->accounts = $accounts;
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
