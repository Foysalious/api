<?php namespace App\Http\Controllers\B2b;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Repositories\ProfileRepository;
use App\Http\Controllers\Controller;
use Sheba\Business\BusinessCommonInformationCreator;
use Sheba\Business\BusinessCreator;
use Sheba\Business\BusinessCreatorRequest;
use Sheba\Business\BusinessMember\Creator as BusinessMemberCreator;
use Sheba\Business\BusinessMember\Requester as BusinessMemberRequester;
use Sheba\Business\BusinessUpdater;
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
    /** BusinessMemberRequester $businessMemberRequester */
    private $businessMemberRequester;
    /** BusinessMemberCreator $businessMemberCreator */
    private $businessMemberCreator;

    /**
     * RegistrationController constructor.
     * @param ProfileRepository $profile_repository
     * @param AccountServer $accounts
     * @param BusinessMemberRequester $business_member_requester
     * @param BusinessMemberCreator $business_member_creator
     */
    public function __construct(ProfileRepository $profile_repository,
                                AccountServer $accounts,
                                BusinessMemberRequester $business_member_requester,
                                BusinessMemberCreator $business_member_creator)
    {
        $this->profileRepository = $profile_repository;
        $this->accounts = $accounts;
        $this->businessMemberRequester = $business_member_requester;
        $this->businessMemberCreator = $business_member_creator;
    }

    /**
     * @param Request $request
     * @param BusinessCreatorRequest $business_creator_request
     * @param BusinessCreator $business_creator
     * @param BusinessUpdater $business_updater
     * @param BusinessCommonInformationCreator $common_info_creator
     * @return JsonResponse
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws SomethingWrongWithToken
     */
    public function registerV3(Request $request, BusinessCreatorRequest $business_creator_request,
                               BusinessCreator $business_creator,
                               BusinessUpdater $business_updater,
                               BusinessCommonInformationCreator $common_info_creator)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'company_name' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);
        $token = $this->accounts->createProfileAndAvatarAndGetTokenByEmailAndPassword('member', $request->name, $request->email, $request->password);
        $auth_user = AuthUser::createFromToken($token);
        /** @var Member $member */
        $member = Member::find($auth_user->getMemberId());
        $this->setModifier($member);

        $business_creator_request = $business_creator_request
            ->setName($request->company_name)
            ->setGeoInformation(json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng]));

        if (count($member->businesses) > 0 && $member->businessMember) {
            $business = $member->businesses->first();
            $business_updater->setBusiness($business)->setBusinessCreatorRequest($business_creator_request)->update();
            $business_member = $member->businessMember;
        } else {
            $business = $business_creator->setBusinessCreatorRequest($business_creator_request)->create();
            $common_info_creator->setBusiness($business)->setMember($member)->create();
            $business_member = $this->createBusinessMember($business, $member);
        }

        $info = [
            'token' => $token,
            'email_verified' => $auth_user->isEmailVerified(),
            'member_id' => $auth_user->getMemberId(),
            'business_id' => $business->id,
            'is_super' => $business_member->is_super
        ];

        return api_response($request, $info, 200, ['info' => $info]);
    }

    /**
     * @param $business
     * @param $member
     * @return Model
     */
    private function createBusinessMember($business, $member)
    {
        $business_member_requester = $this->businessMemberRequester->setBusinessId($business->id)
            ->setMemberId($member->id)
            ->setStatus('active')
            ->setIsSuper(1)
            ->setJoinDate(Carbon::now());
        return $this->businessMemberCreator->setRequester($business_member_requester)->create();
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
