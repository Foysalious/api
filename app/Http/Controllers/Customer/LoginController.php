<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AuthUser;
use Sheba\OAuth2\SomethingWrongWithToken;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;
use Validator;
use DB;

class LoginController extends Controller
{
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var AccountServer $accounts */
    private $accounts;

    /**
     * LoginController constructor.
     * @param ProfileRepository $profile_repository
     * @param AccountServer $accounts
     */
    public function __construct(ProfileRepository $profile_repository, AccountServer $accounts)
    {
        $this->profileRepository = $profile_repository;
        $this->accounts = $accounts;
    }

    /**
     * @param Request $request
     * @param ShebaProfileRepository $profile_repo
     * @return JsonResponse
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking|SomethingWrongWithToken
     */
    public function continueWithKit(Request $request, ShebaProfileRepository $profile_repo)
    {
        $this->validate($request, ['code' => "required", 'from' => 'required|string|in:' . implode(',', constants('FROM'))]);
        $code_data = $this->resolveAccountKit($request->code);
        if (!$code_data) return api_response($request, null, 401);
        $code_data['mobile'] = formatMobile($code_data['mobile']);

        $is_profile_newly_created = 0;

        /** @var Profile $profile */
        $profile = $profile_repo->findByMobile($code_data['mobile'])->first();
        if ($profile && $profile->isBlackListed())
            return api_response($request, null, 403, ['message' => "Your account is blocked."]);

        $from = $this->profileRepository->getAvatar($request->from);

        if (!$profile) {
            $token = $this->accounts->getTokenByShebaAccountKit($request->code);
            $auth_user = AuthUser::createFromToken($token);
            $profile = $auth_user->getProfile();
            $this->profileRepository->registerAvatarByKit($from, $profile);
        }

        if (!$profile->$from) {
            $is_profile_newly_created = 1;
            $this->profileRepository->registerAvatarByKit($from, $profile);
            $profile = $profile->fresh();
        }

        $info = $this->profileRepository->getProfileInfo($from, $profile, $request);
        if (!$info) return api_response($request, null, 404);

        $info['is_new'] = $is_profile_newly_created;
        return api_response($request, null, 200, ['info' => $info]);
    }

    /**
     * @param $code
     * @return array|null
     */
    private function resolveAccountKit($code)
    {
        $access_token_request = new AccessTokenRequest();
        $access_token_request->setAuthorizationCode($code);
        /** @var ShebaAccountKit $account_kit */
        $account_kit = app(ShebaAccountKit::class);
        $mobile = $account_kit->getMobile($access_token_request);
        if (!$mobile) return null;
        return ['mobile' => $mobile];
    }
}
