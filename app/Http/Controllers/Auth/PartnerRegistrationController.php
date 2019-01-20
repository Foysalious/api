<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Library\Sms;
use App\Models\Partner;
use App\Models\PartnerAffiliation;
use App\Models\PartnerBasicInformation;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerWalletSetting;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\ProfileRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Voucher\Creator\Referral;
use DB;

class PartnerRegistrationController extends Controller
{
    private $fbKit;
    private $profileRepository;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
    }

    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'code' => "required|string",
                'company_name' => 'required|string',
                'from' => 'string|in:' . implode(',', constants('FROM')),
                'package_id' => 'exists:partner_subscription_packages,id',
                'billing_type' => 'in:monthly,yearly'
            ]);

            $code_data = $this->fbKit->authenticateKit($request->code);
            if (!$code_data) return api_response($request, null, 401, ['message' => 'AccountKit authorization failed']);
            $mobile = formatMobile($code_data['mobile']);
            if ($profile = $this->profileRepository->ifExist($mobile, 'mobile')) {
                $resource = $profile->resource;
                if (!$resource) $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            } else {
                $profile = $this->profileRepository->registerMobile(array_merge($request->all(), ['mobile' => $mobile]));
                $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            }

            if ($resource->partnerResources->count() > 0) return api_response($request, null, 403, ['message' => 'You already have a company!']);

            $data = $this->makePartnerCreateData($request);
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function registerByResource(Request $request)
    {
        try {
            $this->validate($request, [
                'resource_id' => 'required|int',
                'remember_token' => 'required|string',
                'company_name' => 'required|string',
                'from' => 'string|in:' . implode(',', constants('FROM')),
                'package_id' => 'exists:partner_subscription_packages,id',
                'billing_type' => 'in:monthly,yearly'
            ]);

            $resource = Resource::find($request->resource_id);
            if(!($resource && $resource->remember_token == $request->remember_token)) {
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function makePartnerCreateData(Request $request)
    {
        $data = ['name' => $request->company_name];
        if ($request->has('from')) {
            if ($request->from == 'manager-app' || $request->from == 'affiliation-app') $data['registration_channel'] = constants('PARTNER_ACQUISITION_CHANNEL')['App'];
            elseif ($request->from == 'manager-web') $data['registration_channel'] = constants('PARTNER_ACQUISITION_CHANNEL')['Web'];
        } else {
            $data['registration_channel'] = constants('PARTNER_ACQUISITION_CHANNEL')['App'];
        }
        if ($request->has('billing_type') && $request->has('package_id')) {
            $data['billing_type'] = $request->billing_type;
            $data['package_id'] = $request->package_id;
        }
        if($request->has('affiliate_id')) {
            $data['affiliate_id'] = $request->affiliate_id;
        }
        if($request->has('address')) {
            $data['address'] = $request->address;
        }
        return $data;
    }

    private function createPartner($resource, $data)
    {
        $data = array_merge($data, [
            "sub_domain" => $this->guessSubDomain($data['name']),
            "affiliation_id" => $this->partnerAffiliation($resource->profile->mobile),
        ]);
        $by = ["created_by" => $resource->id, "created_by_name" => "Resource - " . $resource->profile->name];
        $partner = new Partner();
        $partner = $this->store($resource, $data, $by, $partner);
        if ($partner) Sms::send_single_message($resource->profile->mobile, "You have successfully completed your registration at Sheba.xyz. Please complete your profile to start serving orders.");
        return $partner;
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
            ['resource_mobile', $resource_mobile],
            ['status', 'pending']
        ])->first();

        if ($partner_affiliation) return $partner_affiliation->id;
        else return null;
    }

    private function store($resource, $data, $by, $partner)
    {
        try {
            DB::transaction(function () use ($resource, $data, $by, $partner) {
                $partner = $partner->fill(array_merge($data, $by));
                $partner->save();
                $partner->resources()->attach($resource->id, array_merge($by, ['resource_type' => 'Admin']));
                if(isset($data['package_id']) &&(int) $data['package_id'] === 4)
                    $partner->resources()->attach($resource->id, array_merge($by, ['resource_type' => 'Handyman']));

                $partner->basicInformations()->save(new PartnerBasicInformation(array_merge($by, ['is_verified' => 0])));
                (new Referral($partner));
                $this->walletSetting($partner, $by);
                if (isset($data['billing_type']) && isset($data['package_id'])) $partner->subscribe($data['package_id'], $data['billing_type']);
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        }
        return Partner::find($partner->id);
    }

    private function guessSubDomain($name)
    {
        $blacklist = ["google", "facebook", "microsoft", "sheba", "sheba.xyz"];
        $base_name = $name = preg_replace('/-$/', '', substr(strtolower(clean($name)), 0, 15));
        $already_used = Partner::select('sub_domain')->lists('sub_domain')->toArray();
        $counter = 0;
        while (in_array($name, array_merge($blacklist, $already_used))) {
            $name = $base_name . $counter;
            $counter++;
        }
        return $name;
    }

    private function walletSetting($partner, $by)
    {
        $data = ['partner_id' => $partner->id, 'security_money' => 5000];
        PartnerWalletSetting::create(array_merge(['partner_id' => $partner->id, 'security_money' => 5000], $by));
    }

    public function registerReferAffiliate($affiliate, Request $request)
    {
        try {
            $this->validate($request, [
                'company_name' => 'required|string',
                'from' => 'string|in:' . implode(',', constants('FROM')),
                'mobile' => 'required|mobile:bd',
                'name' => 'required'
            ]);

            $mobile = formatMobile($request->mobile);
            if ($profile = $this->profileRepository->ifExist($mobile, 'mobile')) {
                if($profile->name === "" || $profile->name === null) { $profile->name = $request->name;  $profile->save(); }
                $resource = $profile->resource;
                if (!$resource) $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            } else {
                $profile = $this->profileRepository->registerMobile(array_merge($request->all(), ['mobile' => $mobile]));
                $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            }
            $request['package_id'] = env('LITE_PACKAGE_ID');
            $request['billing_type'] = 'monthly';
            $request['affiliate_id'] = (int) $affiliate;

            if(count($resource->partners)>0) {
                $partnerWithAffiliate = (($resource->partners[0]->affiliate_id === (int) $affiliate) && ($resource->partners[0]->status === 'Onboarded'));
                if(!$partnerWithAffiliate || $this->liteFormCompleted($profile,$resource)) return api_response($request, null, 403, ['message' => 'This company already referred!']);
                else {
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function liteFormCompleted($profile, $resource)
    {   
        if(count($resource->partners)=== 0) return false;
        if($profile->name && $profile->mobile && $profile->pro_pic
            && $resource->partners[0]->name && $resource->partners[0]->geo_informations && count($resource->partners[0]->categories)>0)
            return true;
        else
            return false;
    }

}