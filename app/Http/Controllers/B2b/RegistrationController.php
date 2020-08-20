<?php namespace App\Http\Controllers\B2b;

use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Repositories\ProfileRepository;
use App\Http\Controllers\Controller;
use Sheba\Business\CoWorker\Statuses;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Profile;
use JWTAuth;
use JWTFactory;
use Session;
use DB;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\OAuth2\AuthUser;
use Sheba\OAuth2\SomethingWrongWithToken;
use Throwable;

class RegistrationController extends Controller
{
    use ModificationFields;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var AccountServer $accounts */
    private $accounts;

    /**
     * RegistrationController constructor.
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
     * @return JsonResponse
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws SomethingWrongWithToken
     */
    public function registerV3(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        $token = $this->accounts->createProfileAndAvatarAndGetTokenByEmailAndPassword('member', $request->name, $request->email, $request->password);
        $auth_user = AuthUser::createFromToken($token);

        $info = [
            'token' => $token,
            'email_verified' => $auth_user->isEmailVerified(),
            'member_id' => $auth_user->getMemberId(),
            'business_id' => $auth_user->getMemberAssociatedBusinessId(),
            'is_super' => $auth_user->isMemberSuper()
        ];

        return api_response($request, $info, 200, ['info' => $info]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function registerV2(Request $request)
    {
        try {
            $this->validate($request, ['name' => 'required|string', 'email' => 'required|email', 'password' => 'required|min:6']);
            $email = $request->email;
            $profile = Profile::where('email', $email)->first();
            if ($profile) {
                return api_response($request, null, 420, ['message' => 'This email is already in use']);
            } else {
                $data = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ];
                DB::beginTransaction();
                $profile = $this->profileRepository->store($data);
                $member = $this->makeMember($profile);
                // $businesses = $member->businesses->first();
                $businesses = $member->businesses()->wherePivot('status', '<>', Statuses::INACTIVE)->first();
                $info = [
                    'token' => $this->generateToken($profile, $member),
                    'member_id' => $member->id,
                    'business_id' => $businesses ? $businesses->id : null,
                ];
                DB::commit();

                return api_response($request, $info, 200, ['info' => $info]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function generateToken(Profile $profile, $member)
    {
        $businesses = $member->businesses->first();
        return JWTAuth::fromUser($profile, [
            'member_id' => $member->id,
            'member_type' => count($member->businessMember) > 0 ? $member->businessMember->first()->type : null,
            'business_id' => $businesses ? $businesses->id : null,
        ]);
    }

    private function makeMember($profile)
    {
        $this->setModifier($profile);
        $member = new Member();
        $member->profile_id = $profile->id;
        $member->remember_token = str_random(255);
        $member->save();

        return $member;
    }
}
