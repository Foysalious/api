<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Http\Requests\Request;
use App\Models\Partner;
use App\Repositories\ProfileRepository;
use Illuminate\Validation\ValidationException;
use Sheba\Voucher\Creator\Referral;

class PartnerRegistrationController extends Controller
{
    private $fbKit;
    private $profileRepository;
    private $facebookRepository;


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
            if ($code_data = $this->fbKit->authenticateKit($request->code)) {
                return api_response($request, null, 401, ['message' => 'AccountKit authorization failed']);
            }
            $mobile = formatMobile($code_data['mobile']);
            if ($profile = $this->profileRepository->ifExist($mobile, 'mobile')) {
                if ($profile->resource == null) {
                    $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
                }
            } else {
                $profile = $this->profileRepository->registerMobile(array_merge($request->all(), ['mobile' => $mobile]));
                $resource = $this->profileRepository->registerAvatarByKit('resource', $profile);
            }
            if (empty($profile->name)) {
                $profile->name = $request->name;
                $profile->update();
                $resource = $profile->resource;
            }
            $this->createPartner($resource, ['name' => $request->company_name]);
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
        $partner = Partner::create(array_merge($data, $by));
        $partner->resources()->attach($resource->id, array_merge($by, ['resource_type' => 'Admin']));
        $partner->basicInformations()->save(array_merge($by, ['is_verified' => 0]));
        //(new ReferralCreator($partner))->create(); //Create referrel code
        (new Referral($partner));
        $this->walletSetting($partner);

        return $partner;
    }

    private function walletSetting($partner)
    {
        $data = [
            'partner_id' => $partner->id,
            'security_money' => 5000,
        ];
        PartnerWalletSetting::create($this->withCreateModificationField($data));
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

    public function created(Partner $partner)
    {
        return view('partner.created', compact('partner'));
    }


}