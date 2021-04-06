<?php namespace App\Http\Controllers\B2b;

use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use App\Repositories\ProfileRepository;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Sheba\Business\BusinessCommonInformationCreator;
use Sheba\Business\BusinessCreator;
use Sheba\Business\BusinessCreatorRequest;
use Sheba\Business\BusinessMember\Creator as BusinessMemberCreator;
use Sheba\Business\BusinessMember\Requester as BusinessMemberRequester;
use Sheba\Business\BusinessUpdater;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Sheba\OAuth2\AccountServer;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Throwable;

class RegistrationController extends Controller
{
    use ModificationFields;

    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /**@var MemberRepositoryInterface $memberRepository */
    private $memberRepository;
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
     * @param MemberRepositoryInterface $member_repository
     */
    public function __construct(ProfileRepository $profile_repository,
                                AccountServer $accounts,
                                BusinessMemberRequester $business_member_requester,
                                BusinessMemberCreator $business_member_creator,
                                MemberRepositoryInterface $member_repository)
    {
        $this->profileRepository = $profile_repository;
        $this->accounts = $accounts;
        $this->businessMemberRequester = $business_member_requester;
        $this->businessMemberCreator = $business_member_creator;
        $this->memberRepository = $member_repository;
    }

    /**
     * @param Request $request
     * @param BusinessCreatorRequest $business_creator_request
     * @param BusinessCreator $business_creator
     * @param BusinessUpdater $business_updater
     * @param BusinessCommonInformationCreator $common_info_creator
     * @return JsonResponse
     */
    public function registerV3(Request $request, BusinessCreatorRequest $business_creator_request,
                               BusinessCreator $business_creator,
                               BusinessUpdater $business_updater,
                               BusinessCommonInformationCreator $common_info_creator)
    {
        try {
            $this->validate($request, [
                'company_name' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric'
            ]);
            /** @var Profile $profile */
            $profile = $request->profile;

            DB::beginTransaction();

            /** @var Member $member */
            $profile->member ? $member = $profile->member : $member = $this->createMember($profile);
            $this->setModifier($member);

            $business_creator_request = $business_creator_request
                ->setName($request->company_name)
                ->setGeoInformation(json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng]));

            if ($member->businessMember) return api_response($request, null, 409, ['message' => "This business is already added"]);

            $business = $business_creator->setBusinessCreatorRequest($business_creator_request)->create();
            $common_info_creator->setBusiness($business)->setMember($member)->create();
            $business_member = $this->createBusinessMember($business, $member);
            DB::commit();
            $info = [
                'email_verified' => $profile->email_verified,
                'member_id' => $member->id,
                'business_id' => $business->id,
                'is_super' => $business_member->is_super
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $profile
     * @return Model
     */
    private function createMember($profile)
    {
        $this->setModifier($profile);
        return $this->memberRepository->create([
            'profile_id' => $profile->id,
            'remember_token' => str_random(255)
        ]);
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
}
