<?php namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Jobs\Business\SendMailVerificationCodeEmail;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\OAuth2\AuthUser;
use Sheba\OAuth2\SomethingWrongWithToken;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Throwable;

class ProfileController extends Controller
{
    public function checkProfile(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
            ]);
            $profile = Profile::where('email', $request->email)->first();
            if ($profile) {
                return api_response($request, null, 420, ['message' => 'This profile already exist']);
            }
            return api_response($request, null, 401, ['message' => 'Create Profile First']);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param ProfileRepositoryInterface $profileRepository
     * @param AccountServer $accounts
     * @return JsonResponse
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws SomethingWrongWithToken
     */
    public function verifyEmailWithVerificationCode(Request $request, ProfileRepositoryInterface $profileRepository, AccountServer $accounts)
    {
        $this->validate($request, ['code' => 'required', 'token' => 'required']);
        $code = Redis::get('email_verification_code_' . $request->code);
        if (!$code) return api_response($request, null, 404, ['message' => 'Code did not match']);

        $code = json_decode($code, 1);
        $profile = $profileRepository->find($code['profile_id']);
        $profileRepository->update($profile, ['email_verified' => 1, 'email_verified_at' => Carbon::now()]);
        $token = $accounts->getRefreshToken($request->token);
        $auth_user = AuthUser::createFromToken($token);
        $info = [
            'token' => $token,
            'email_verified' => $auth_user->isEmailVerified(),
            'member_id' => $auth_user->getMemberId(),
            'business_id' => $auth_user->getMemberAssociatedBusinessId(),
            'is_super' => $auth_user->isMemberSuper()
        ];
        return api_response($request, null, 200, ['info' => $info]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param AccountServer $accounts
     * @return JsonResponse
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     */
    public function refreshToken($business, Request $request, AccountServer $accounts)
    {
        $this->validate($request, ['token' => 'required']);
        $token = $accounts->getRefreshToken($request->token);
        return api_response($request, null, 200, ['token' => $token]);
    }

    public function sendEmailVerificationCode(Request $request)
    {
        try {
            $profile = $request->profile;
            $this->dispatch(new SendMailVerificationCodeEmail($profile));
            return api_response($request, null, 200);
        } catch (Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function sendEmailVerificationLink(Request $request, AccountServer $accounts)
    {
        try {
            $accounts->sendEmailVerificationLink($request->token);
            return api_response($request, null, 200);
        } catch (Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getInfo(Request $request)
    {
        try {
            return api_response($request, true, 200, ['data' => [
                'is_registered' => $request->is_registered ? (int)$request->is_registered : 0,
                'has_password' => $request->has_password ? (int)$request->has_password : 0,
                'has_partner' => $request->has_partner ? (int)$request->has_partner : 0,
                'has_resource' => $request->has_resource ? (int)$request->has_resource : 0,
            ]]);
        } catch (ValidationException $e) {
            return api_response($request, null, 401, ['message' => 'Invalid mobile number']);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPartnerInfo(Request $request)
    {
        try {
            return api_response($request, true, 200, [
                'data' => [
                    'partner' => [
                        'id' => 1,
                        'name' => 'adad',
                        'mobile' => '+88017589',
                        'address' => 'afaf',
                        'geo' => [
                            'lat' => 455,
                            'lng' => 47,
                            'radius' => 5
                        ],
                        'categories' => [
                            ['id' => 4, 'name' => 'ad'],
                            ['id' => 5, 'name' => 'af'],
                            ['id' => 6, 'name' => 'aafafd'],
                        ]
                    ],
                    'resource' => [
                        'id' => 1,
                        'name' => 'adad',
                        'token' => str_random(30)
                    ]
                ]
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDetail(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile:bd'
        ]);

        $profile = Profile::where('mobile', formatMobile($request->mobile))->first();

        if (!$profile) return api_response($request, null, 404, ['message' => 'Profile Not Found']);
        return api_response($request, null, 200, ['profile' => [
            'name' => $profile->name
        ]]);
    }
}
