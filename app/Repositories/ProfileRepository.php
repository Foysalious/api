<?php namespace App\Repositories;

use App\Http\Validators\MobileNumberValidator;
use App\Models\Affiliate;
use App\Models\Member;
use App\Models\Promotion;
use App\Models\Voucher;
use App\Models\AffiliateTransaction;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;
use App\Models\Profile;
use App\Models\Resource;
use DB;
use Auth;
use Sheba\ModificationFields;
use Sheba\OAuth2\AccountServer;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\Voucher\Creator\Referral;

class ProfileRepository
{
    use ModificationFields;

    /** @var AccountServer */
    private $accountServer;

    public function __construct()
    {
        $this->accountServer = app(AccountServer::class);
    }

    public function getIfExist($data, $queryColumn)
    {
        $profile = Profile::where($queryColumn, $data)->where('is_blacklisted', 0)->first();
        return $profile != null ? $profile : null;
    }

    public function store(array $data)
    {
        $profile                 = new Profile();
        $profile->remember_token = str_random(255);
        foreach ($data as $key => $value) {
            $profile->$key = $value;
        }
        $profile->save();
        return $profile;
    }

    public function updateIfNull($profile, array $data)
    {
        foreach ($data as $key => $value) {
            if (empty($profile->$key) || $profile->$key == null || !$profile->$key) {
                $profile->$key = $value;
            }
        }
        $profile->update();
        return $profile;
    }

    public function update(Profile $profile, array $data)
    {
        $profile->update($this->withUpdateModificationField($data));
        return $profile;
    }

    public function ifExist($data, $queryColumn)
    {
        $user = Profile::where($queryColumn, $data)->where('is_blacklisted', 0)->first();
        return $user != null ? $user : false;
    }

    public function registerEmail($request)
    {
        $profile = Profile::create([
            'email'          => $request->email,
            'password'       => bcrypt($request->password),
            'remember_token' => str_random(255)
        ]);
        return Profile::find($profile->id);
    }

    /**
     * @param $from
     * @param Profile $profile
     * @param null $request
     * @return array|null
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getProfileInfo($from, Profile $profile, $request = null)
    {
        $avatar = $profile->$from;
        if ($avatar != null) {
            $info = [
                'id'            => $avatar->id,
                'name'          => $profile->name,
                'mobile'        => $profile->mobile,
                'email'         => $profile->email,
                'profile_image' => $profile->pro_pic,
                'token'         => $avatar->remember_token
            ];

            if ($from == 'affiliate') {
                $info['name']                = $profile->name;
                $info['mobile']              = $profile->mobile;
                $info['bKash']               = $avatar->banking_info->bKash;
                $info['verification_status'] = $avatar->verification_status;
                $info['is_suspended']        = $avatar->is_suspended;
                $info['ambassador_code']     = $avatar->is_ambassador ? $avatar->referral->code : null;
                $info['is_ambassador']       = $avatar->is_ambassador;
            } elseif ($from == 'bankUser') {
                $info['bank_logo'] = $avatar->bank->logo;
                $info['bank_name'] = $avatar->bank->name;
                $defaultPass       = 'ShebaAdmin#1';
                if ($request->key == $defaultPass || $request->key == 'ShebaAdmin')
                    $info['has_changed_password'] = 0;
                else
                    $info['has_changed_password'] = 1;
            } elseif ($from == 'customer') {
                $info['referral']     = $avatar->referral ? $avatar->referral->code : '';
                $info['order_count']  = $avatar->orders->count();
                $info['voucher_code'] = constants('APP_VOUCHER');
                $info['referrer_id']  = $avatar->referrer_id;
            } elseif ($from == 'resource') {
                $resource_types                    = $avatar->partnerResources->pluck('resource_type')->toArray();
                $info['is_handyman']               = count($resource_types) > 0 ? !isResourceAdmin($resource_types) : false;
                $info['is_profile_avatar_default'] = (getProfileDefaultAvatar() === $avatar->profile->pro_pic);
                $info['is_verified']               = $avatar->is_verified;
                $info['partners']                  = $avatar->partners->unique('partner_id')->count();
                $info['partner']                   = (new ResourceRepository($avatar))->getPartner($avatar);
                $info['email']                     = $profile->email ? $profile->email : (strtolower(clean($profile->name, '_')) . "@ajaira.co");
            } elseif ($from == 'strategicPartnerMember') {
                $info['strategic_partner']       = $avatar->strategicPartner ? [
                    'name'=>$avatar->strategicPartner->name,
                    'logo'=>$avatar->strategicPartner->logo,
                    'id'=>$avatar->strategicPartner->id
                ] : null;
                $info['remember_token'] = $avatar->remember_token;
                $info['role']           = $avatar->role;
                $defaultPass            = 'ShebaAdmin#1';
                if ($request->key == $defaultPass || $request->key == 'ShebaAdmin')
                    $info['has_changed_password'] = 0;
                else
                    $info['has_changed_password'] = 1;
            }

//            $info['token'] = $this->getJwtToken($avatar, $from);
            $info['auth_token'] = $this->getJwtToken($avatar, $from);
            return $info;
        }

        return null;
    }

    /**
     * @param $avatar
     * @param $from
     * @return mixed
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function getJwtToken($avatar, $from)
    {
        return $this->accountServer->getTokenByAvatar($avatar, $from);
    }

    /**
     * @param $type
     * @param $type_id
     * @param $remember_token
     * @return mixed
     * @throws AccountServerNotWorking
     * @throws AccountServerAuthenticationError
     */
    public function fetchJWTToken($type, $type_id, $remember_token)
    {
        return $this->accountServer->getTokenByIdAndRememberToken($type_id, $remember_token, $type);
    }

    public function registerFacebook($info)
    {
        $profile        = new Profile();
        $profile->fb_id = $info['fb_id'];
        $profile->name  = $info['fb_name'];
        $profile->portal_name  = $info['portal_name'];
        $profile->email = $info['fb_email'] != 'undefined' ? $info['fb_email'] : null;
        if ($profile->email != null) {
            $profile->email_verified = 1;
        }
        $profile->gender         = isset($info['fb_gender']) ? $info['fb_gender'] : '';
        $profile->pro_pic        = $info['fb_picture'];
        $profile->remember_token = str_random(255);
        $profile->save();
        return $profile;
    }

    public function uploadImage($profile, $photo, $folder, $extension = ".jpg")
    {
        $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . $extension;
        $s3       = Storage::disk('s3');
        $s3->put($folder . $filename, file_get_contents($photo), 'public');
        return config('s3.url') . $folder . $filename;
    }

    public function registerAvatarByFacebook($avatar, $request, $user)
    {
        if ($avatar == 'customer') {
            $customer = Customer::create([
                'remember_token' => str_random(255),
                'profile_id'     => $user->id
            ]);
            $customer = Customer::find($customer->id);
            $this->setModifier($customer);
            new Referral($customer);

            if ($request->has('referral_code')) {
                $this->updateCustomerOwnVoucherNReferral($customer, $request->referral_code);
            }
        } elseif ($avatar == 'resource') {
            $resource                 = new Resource();
            $resource->profile_id     = $user->id;
            $resource->remember_token = str_random(255);
            $resource->save();
            return $resource;
        }
    }

    public function integrateFacebook($profile, $request)
    {
        $profile->fb_id = $request->fb_id;
        if (empty($profile->name)) {
            $profile->name = $request->fb_name;
        }
        if (empty($profile->gender) && $request->has('fb_gender')) {
            $profile->gender = $request->fb_gender;
        }
        if (empty($profile->pro_pic) || basename($profile->pro_pic) == 'default.jpg') {
            $profile->pro_pic = $this->uploadImage($profile, $request->fb_picture, 'images/profiles/');
        }
        if ($profile->email_verified == 0) {
            $profile->email_verified = 1;
        }
        $profile->update();
        return $profile;
    }

    private function updateCustomerOwnVoucherNReferral($customer, $referrer)
    {
        $voucher = Voucher::where('code', $referrer)->first();
        if ($voucher == null) {
            return;
        }
        $this->addReferrerIdInCustomer($customer, $voucher);
        $this->addToPromoList($customer, $voucher);
    }

    private function addReferrerIdInCustomer($customer, $voucher)
    {
        $customer->referrer_id = $voucher->owner_id;
        $customer->update();
    }

    public function addToPromoList($customer, $voucher)
    {
        $promo              = new Promotion();
        $promo->customer_id = $customer->id;
        $promo->voucher_id  = $voucher->id;
        $promo->is_valid    = 1;
        $date               = Carbon::now()->addDays(constants('REFERRAL_VALID_DAYS'));
        $promo->valid_till  = $date->toDateString() . " 23:59:59";
        return $promo->save();
    }

    /**
     * @param $info
     * @return mixed
     */
    public function registerMobile($info)
    {
        $data = [
            'mobile'          => $info['mobile'],
            'portal_name'     => isset($info['portal_name']) ? $info['portal_name'] : $info['from'],
            'mobile_verified' => 1,
            "remember_token" => str_random(255)
        ];
        if (isset($info['name'])) $data['name'] = $info['name'];
        $profile = Profile::create($data);

        return Profile::find($profile->id);
    }

    /**
     * @param $avatar
     * @param $request
     * @param Profile $profile
     * @return Customer|Resource
     */
    public function registerAvatar($avatar, $request, Profile $profile)
    {
        if ($avatar == 'customer') {
            $customer                 = new Customer();
            $customer->profile_id     = $profile->id;
            $customer->remember_token = str_random(255);
            $customer->save();
            $this->setModifier($customer);
            new Referral($customer);
            if ($request->has('referral_code')) {
                $this->updateCustomerOwnVoucherNReferral($customer, $request->referral_code);
            }
            return $customer;
        } elseif ($avatar == 'resource') {
            $resource                 = new Resource();
            $resource->profile_id     = $profile->id;
            $resource->remember_token = str_random(255);
            $resource->save();
            return $resource;
        } elseif ($avatar == env('AFFILIATE_AVATAR_NAME')) {
            $affiliate                 = new Affiliate();
            $affiliate->profile_id     = $profile->id;
            $affiliate->remember_token = str_random(255);
            $affiliate->banking_info   = json_encode(array('bKash' => ''));
            $affiliate->save();
            (new NotificationRepository())->forAffiliateRegistration($affiliate);
        }
    }

    /**
     * @param $avatar
     * @param $user
     * @return Resource
     */
    public function registerAvatarByKit($avatar, $user)
    {
        if ($avatar == 'customer') {
            $customer = Customer::create([
                'remember_token' => str_random(255),
                'profile_id'     => $user->id
            ]);
            $customer = Customer::find($customer->id);
            $this->setModifier($customer);
            new Referral($customer);
        } elseif ($avatar == 'resource') {
            $resource                 = new Resource();
            $resource->profile_id     = $user->id;
            $resource->remember_token = str_random(255);
            $resource->status = $user->affiliate ? $user->affiliate->verification_status : 'unverified';
            $resource->save();
            return $resource;
        } elseif ($avatar == env('AFFILIATE_AVATAR_NAME')) {
            $affiliate                 = new Affiliate();
            $affiliate->profile_id     = $user->id;
            $affiliate->remember_token = str_random(255);
            $affiliate->banking_info   = json_encode(array('bKash' => ''));
            $affiliate->save();
            if ((new MobileNumberValidator())->validateBangladeshi($user->mobile)) $this->addAffiliateBonus($affiliate);
            (new NotificationRepository())->forAffiliateRegistration($affiliate);
        }
    }

    private function addAffiliateBonus(Affiliate $affiliate)
    {
        $affiliate_bonus_amount = constants('AFFILIATION_REGISTRATION_BONUS');

        DB::transaction(function () use ($affiliate, $affiliate_bonus_amount) {
            $affiliate->update([
                'wallet'           => $affiliate_bonus_amount,
                'acquisition_cost' => $affiliate_bonus_amount
            ]);

            AffiliateTransaction::create([
                'affiliate_id' => $affiliate->id,
                'type'         => 'Credit',
                'log'          => "Affiliate earned $affiliate_bonus_amount point for registration",
                'amount'       => $affiliate_bonus_amount
            ]);
        });

        (new SmsHandler('affiliate-register'))
            ->setBusinessType(BusinessType::BONDHU)
            ->setFeatureType(FeatureType::AFFILIATE_BONUS)
            ->send($affiliate->profile->mobile, [
                'bonus_amount' => $affiliate_bonus_amount
            ]);
    }

    public function registerAvatarByEmail($avatar, $request, $user)
    {
        if ($avatar == 'customer') {
            $customer = Customer::create([
                'remember_token' => str_random(255),
                'profile_id'     => $user->id
            ]);
            $customer = Customer::find($customer->id);
            $this->setModifier($customer);
            new Referral($customer);
            if ($request->has('referral_code')) {
                $this->updateCustomerOwnVoucherNReferral($customer, $request->referral_code);
            }
        } elseif ($avatar == 'resource') {
            $resource                 = new Resource();
            $resource->profile_id     = $user->id;
            $resource->remember_token = str_random(255);
            $resource->save();
        } elseif ($avatar == 'member') {
            $member                 = new Member();
            $member->remember_token = str_random(255);
            $member->profile_id     = $user->id;
            $member->save();
            return $member;
        }
    }

    public function getAvatar($from)
    {
        return constants('AVATAR')[$from];
    }

    public function getByEmail($email)
    {
        return Profile::where('email', $email)->first();
    }
}
