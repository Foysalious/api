<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;

use App\Models\BusinessJoinRequest;
use App\Models\Partner;
use App\Models\PartnerAffiliation;
use App\Models\PartnerBasicInformation;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerWalletSetting;
use App\Models\Profile;
use App\Models\Resource;

use App\Repositories\PartnerRepository;
use App\Repositories\ProfileRepository;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\EntryRepository;
use Sheba\Logs\ErrorLog;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\Partner\PartnerRepositoryInterface;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Sms\Sms;
use Sheba\Voucher\Creator\Referral;
use DB;
use Throwable;

class PartnerRegistrationController extends Controller
{
    use ModificationFields;

    /** @var FacebookAccountKit $fbKit */
    private $fbKit;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var Sms */
    private $sms;
    /** @var EntryRepository $entryRepo */
    private $entryRepo;
    /** @var PartnerRepositoryInterface $partnerRepo */
    private $partnerRepo;

    public function __construct(EntryRepository $entry_repo, PartnerRepositoryInterface $partner_repo)
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
        $this->sms = new Sms();
        $this->entryRepo = $entry_repo;
        $this->partnerRepo = $partner_repo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getWelcomeMessage(Request $request)
    {
        try {
            $data = [
                'image' => config('s3.url') . "images/manager_app/offer_v2.jpeg", 'message' => 'বোনাস ব্যবহার করে sManager অ্যাপ  সাবস্ক্রাইব করুন, পুরো এক মাসের জন্য  সম্পুর্ন ফ্রি'
            ];
            return api_response($request, null, 200, ['info' => $data]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'code' => "required|string", 'company_name' => 'required|string', 'from' => 'string|in:' . implode(',', constants('FROM')), 'package_id' => 'exists:partner_subscription_packages,id', 'billing_type' => 'in:monthly,yearly'
            ]);

            $code_data = $this->fbKit->authenticateKit($request->code);
            if (!$code_data) return api_response($request, null, 401, ['message' => 'AccountKit authorization failed']);
            $mobile = formatMobile($code_data['mobile']);

            if ($profile = $this->profileRepository->ifExist($mobile, 'mobile')) {
                $resource = $profile->resource;
                if (!$resource) {
                    $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
                }
            } else {
                $profile = $this->profileRepository->registerMobile(array_merge($request->all(), ['mobile' => $mobile]));
                $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            }
            if ($resource->partnerResources->count() > 0) return api_response($request, null, 403, ['message' => 'You already have a company!']);

            $data = $this->makePartnerCreateData($request);
            if ($partner = $this->createPartner($resource, $data)) {
                $info = $this->profileRepository->getProfileInfo('resource', Profile::find($profile->id));
                $business_join_reqs = BusinessJoinRequest::where('mobile', $mobile)->first();
                if ($business_join_reqs) {
                    $partner->businesses()->sync(['business_id' => $business_join_reqs->business_id]);
                    $business_join_reqs->update(['status' => 'successful']);
                }
                return api_response($request, null, 200, ['info' => $info]);
            } else {
                return api_response($request, null, 500);
            }
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
     * @return array
     */
    private function makePartnerCreateData(Request $request)
    {
        $data = ['name' => $request->company_name];
        if ($request->has('from')) {
            if ($request->from == 'manager-app' || $request->from == 'affiliation-app') $data['registration_channel'] = constants('PARTNER_ACQUISITION_CHANNEL')['App']; elseif ($request->from == 'manager-web') $data['registration_channel'] = constants('PARTNER_ACQUISITION_CHANNEL')['Web'];
        } else {
            $data['registration_channel'] = constants('PARTNER_ACQUISITION_CHANNEL')['App'];
        }
        if ($request->has('billing_type') && $request->has('package_id')) {
            $data['billing_type'] = $request->billing_type;
            $data['package_id'] = $request->package_id;
            $data['billing_start_date'] = Carbon::today();
            $data['last_billed_date'] = Carbon::today();
            $data['last_billed_amount'] = 0.00;
        }
        if ($request->has('affiliate_id')) {
            $data['affiliate_id'] = $request->affiliate_id;
        }
        if ($request->has('address')) {
            $data['address'] = $request->address;
        }
        if ($request->has('number')) $data['mobile'] = formatMobile($request->number);
        if ($request->has('geo')) {
            $geo = json_decode($request->geo);
            $geo->radius = 5;
            $data['geo_informations'] = json_encode($geo);
        }
        return $data;
    }

    /**
     * @param $resource
     * @param $data
     * @return Partner
     * @throws ExpenseTrackingServerError
     */
    private function createPartner($resource, $data)
    {
        $data = array_merge($data, [
            "sub_domain" => $this->guessSubDomain($data['name']), "affiliation_id" => $this->partnerAffiliation($resource->profile->mobile),
        ]);
        $by = ["created_by" => $resource->id, "created_by_name" => "Resource - " . $resource->profile->name];
        /** @var Partner $partner */
        $partner = new Partner();
        $partner = $this->store($resource, $data, $by, $partner);
        if ($partner) {
            $this->sms->shoot($resource->profile->mobile, "অভিনন্দন! sManager-এ আপনি সফল ভাবে রেজিস্ট্রেশন সম্পন্ন করেছেন। বিস্তারিত দেখুন: http://bit.ly/sManagerGettingStarted");
            $partner->refer_code = $partner->referCode();
            $partner->save();
        }

        app()->make(ActionRewardDispatcher::class)->run('partner_creation_bonus', $partner, $partner);

        $this->storeExpense($partner);
        return $partner;
    }

    /**
     * @param $name
     * @return string|string[]|null
     */
    private function guessSubDomain($name)
    {
        $blacklist = ["google", "facebook", "microsoft", "sheba", "sheba.xyz"];
        $base_name = $name = preg_replace('/-$/', '', substr(strtolower(clean($name)), 0, 15));
        $already_used = Partner::select('sub_domain')->where('sub_domain', 'like', $name . '%')->lists('sub_domain')->toArray();
        $counter = 0;
        while (in_array($name, array_merge($blacklist, $already_used))) {
            $name = $base_name . $counter;
            $counter++;
        }
        return $name;
    }

    /**
     * CHECK THIS PARTNER CREATED FROM AFFILIATION,
     * IF FROM AFFILIATION SET PARTNER AFFILIATION ID
     *
     * @param $resource_mobile
     * @return null
     */
    private function partnerAffiliation($resource_mobile)
    {
        $partner_affiliation = PartnerAffiliation::where([
            ['resource_mobile', $resource_mobile], ['status', 'pending']
        ])->first();

        if ($partner_affiliation) return $partner_affiliation->id; else return null;
    }

    private function store($resource, $data, $by, $partner)
    {
        try {
            DB::transaction(function () use ($resource, $data, $by, $partner) {
                $partner = $partner->fill(array_merge($data, $by));
                $partner->save();
                $partner->resources()->attach($resource->id, array_merge($by, ['resource_type' => 'Admin']));
                if (isset($data['package_id']) && $data['package_id'] == env('LITE_PACKAGE_ID')) {
                    $partner->resources()->attach($resource->id, array_merge($by, ['resource_type' => 'Handyman']));
                    (new PartnerRepository($partner))->saveDefaultWorkingHours($by);
                }

                $partner->basicInformations()->save(new PartnerBasicInformation(array_merge($by, ['is_verified' => 0])));
                (new Referral($partner));
                $this->walletSetting($partner, $by);
                if (isset($data['billing_type']) && isset($data['package_id'])) $partner->subscribe($data['package_id'], $data['billing_type']);
            });
        } catch (QueryException $e) {
            throw  $e;
        }
        return Partner::find($partner->id);
    }

    private function walletSetting($partner, $by)
    {
        $data = ['partner_id' => $partner->id, 'security_money' => constants('PARTNER_DEFAULT_SECURITY_MONEY')];
        PartnerWalletSetting::create(array_merge(['partner_id' => $partner->id, 'security_money' => constants('PARTNER_DEFAULT_SECURITY_MONEY')], $by));
    }

    public function registerByProfile(Request $request, ErrorLog $error_log)
    {
        try {
            $this->validate($request, [
                'company_name' => 'required|string',
                'from' => 'string|in:' . implode(',', constants('FROM')),
                'geo' => 'string',
                'name' => 'string',
                'number' => 'string',
                'address' => 'string'
            ]);
            $profile = $request->profile;

            if (!$profile->resource) $resource = Resource::create(['profile_id' => $profile->id, 'remember_token' => str_random(60)]);
            else $resource = $profile->resource;

            $this->setModifier($resource);

            $request['package_id'] = config('sheba.partner_lite_packages_id');
            $request['billing_type'] = 'monthly';
            if ($request->has('name')) $profile->update(['name' => $request->name]);
            if ($request->has('gender')) $profile->update(['gender' => $request->gender]);

            if ($resource->partnerResources->count() == 0) {
                $data = $this->makePartnerCreateData($request);
                $this->createPartner($resource, $data);
                $info = $this->profileRepository->getProfileInfo('resource', $profile);
                return api_response($request, null, 200, ['info' => $info]);
            } else {
                return api_response($request, null, 403, ['message' => 'You already have a company.']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $error_log->setException($e)->setRequest($request)->setErrorMessage($message)->send();
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            $error_log->setException($e)->send();
            return api_response($request, null, 500);
        }
    }

    public function registerByResource(Request $request)
    {
        try {
            $this->validate($request, [
                'resource_id' => 'required|int', 'remember_token' => 'required|string', 'company_name' => 'required|string', 'from' => 'string|in:' . implode(',', constants('FROM')), 'package_id' => 'exists:partner_subscription_packages,id', 'billing_type' => 'in:monthly,yearly'
            ]);

            $resource = Resource::find($request->resource_id);
            if (!($resource && $resource->remember_token == $request->remember_token)) {
                return api_response($request, null, 403, ['message' => "Unauthorized."]);
            }

            $profile = $resource->profile;
            $profile->name = $request->name;
            $profile->save();

            if ($resource->partnerResources->count() > 0) return api_response($request, null, 403, ['message' => 'You already have a company!']);

            $request['package_id'] = env('LITE_PACKAGE_ID');
            $request['billing_type'] = 'monthly';

            $data = $this->makePartnerCreateData($request);
            if ($partner = $this->createPartner($resource, $data)) {
                $info = $this->profileRepository->getProfileInfo('resource', Profile::find($profile->id));
                return api_response($request, null, 200, ['info' => $info]);
            } else {
                return api_response($request, null, 500);
            }
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

    public function registerReferAffiliate($affiliate, Request $request)
    {
        try {
            $this->validate($request, [
                'company_name' => 'required|string', 'from' => 'string|in:' . implode(',', constants('FROM')), 'mobile' => 'required|mobile:bd', 'name' => 'required'
            ]);

            $mobile = formatMobile($request->mobile);
            if ($profile = $this->profileRepository->ifExist($mobile, 'mobile')) {
                if ($profile->name === "" || $profile->name === null) {
                    $profile->name = $request->name;
                    $profile->save();
                }
                $resource = $profile->resource;
                if (!$resource) $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            } else {
                $profile = $this->profileRepository->registerMobile(array_merge($request->all(), ['mobile' => $mobile]));
                $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            }
            $request['package_id'] = env('LITE_PACKAGE_ID');
            $request['billing_type'] = 'monthly';
            $request['affiliate_id'] = (int)$affiliate;

            if (count($resource->partners) > 0) {
                $partnerWithAffiliate = (($resource->partners[0]->affiliate_id === (int)$affiliate) && ($resource->partners[0]->status === 'Onboarded'));
                if (!$partnerWithAffiliate || $this->liteFormCompleted($profile, $resource)) return api_response($request, null, 403, ['message' => 'This company already referred!']); else {
                    $data = $this->makePartnerCreateData($request);
                    $data['moderation_status'] = 'pending';
                    $partner = $resource->partners[0];
                    $partner->update($data);
                    $info = $this->profileRepository->getProfileInfo('resource', Profile::find($profile->id));
                    return api_response($request, null, 200, ['info' => $info]);
                }
            }

            $data = $this->makePartnerCreateData($request);
            $data['moderation_status'] = 'pending';
            if ($partner = $this->createPartner($resource, $data)) {
                $info = $this->profileRepository->getProfileInfo('resource', Profile::find($profile->id));
                /**
                 * LOGIC CHANGE - PARTNER REWARD MOVE TO WAITING STATUS
                 *
                 * app('\Sheba\PartnerAffiliation\RewardHandler')->setPartner($partner)->onBoarded();
                 */
                return api_response($request, null, 200, ['info' => $info]);
            } else {
                return api_response($request, null, 500);
            }
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

    private function liteFormCompleted($profile, $resource)
    {
        if (count($resource->partners) === 0) return false;
        if ($profile->name && $profile->mobile && $profile->pro_pic && $resource->partners[0]->name && $resource->partners[0]->geo_informations && count($resource->partners[0]->categories) > 0) return true; else return false;
    }

    /**
     * @param $partner
     * @throws ExpenseTrackingServerError
     */
    private function storeExpense($partner)
    {
        $account = $this->entryRepo->createExpenseUser($partner);
        $data = ['expense_account_id' => $account['id']];
        $this->partnerRepo->update($partner, $data);
    }
}
