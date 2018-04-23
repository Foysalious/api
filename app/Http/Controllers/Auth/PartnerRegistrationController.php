<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Partner;
use App\Models\PartnerBasicInformation;
use App\Models\PartnerWalletSetting;
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
                'code' => "required",
                'name' => 'required',
                'company_name' => 'required',
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
            $profile = $this->profileRepository->updateIfNull($profile, ['name' => $request->name]);
            if ($partner = $this->createPartner($resource, ['name' => $request->company_name])) {
                return api_response($request, null, 200);
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


    private function createPartner($resource, $data)
    {
        $data = [
            "name" => $data['name'],
            "sub_domain" => $this->guessSubDomain($data['name'])
        ];
        $by = [
            "created_by" => $resource->id,
            "created_by_name" => "Resource - " . $resource->profile->name
        ];
        $partner = new Partner();
        try {
            DB::transaction(function () use ($resource, $data, $by, $partner) {
                $partner = $partner->fill(array_merge($data, $by));
                $partner->save();
                $partner->resources()->attach($resource->id, array_merge($by, ['resource_type' => 'Admin']));
                $partner->basicInformations()->save(new PartnerBasicInformation(array_merge($by, ['is_verified' => 0])));
                (new Referral($partner));
                $this->walletSetting($partner, $by);
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        }
        return $partner;
    }

    private function walletSetting($partner, $by)
    {
        $data = [
            'partner_id' => $partner->id,
            'security_money' => 5000,
        ];
        PartnerWalletSetting::create(array_merge($data, $by));
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

}