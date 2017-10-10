<?php

namespace App\Repositories;

use App\Models\Affiliate;
use App\Models\Member;
use App\Models\Promotion;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;
use App\Models\Profile;
use App\Models\Resource;
use DB;
use Auth;
use Mockery\Exception;
use Sheba\Voucher\ReferralCreator;

class ProfileRepository
{
    /**
     * Check if user already exists
     * @param $data
     * @param $queryColumn
     * @return bool
     */
    public function ifExist($data, $queryColumn)
    {
        $user = Profile::where($queryColumn, $data)->first();
        if ($user != null) {
            return $user;
        } else {
            return false;
        }
    }

    public function registerEmail($request)
    {
        $profile = Profile::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'remember_token' => str_random(255)
        ]);
        return Profile::find($profile->id);
    }

    public function getProfileInfo($from, Profile $profile)
    {
        $avatar = $profile->$from;
        if ($avatar != null) {
            $info = array(
                'id' => $avatar->id,
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'profile_image' => $profile->pro_pic,
                'token' => $avatar->remember_token,
            );
            if ($from == 'affiliate') {
                $info['name'] = $profile->name;
                $info['mobile'] = $profile->mobile;
                $info['bKash'] = $avatar->banking_info->bKash;
                $info['verification_status'] = $avatar->verification_status;
                $info['is_suspended'] = $avatar->is_suspended;
                $info['ambassador_code'] = $avatar->ambassador_code;
                $info['is_ambassador'] = $avatar->is_ambassador;
            } elseif ($from == 'customer') {
                $info['referral'] = $avatar->referral->code;
                $info['order_count'] = $avatar->orders->count();
                $info['voucher_code'] = constants('APP_VOUCHER');
            } elseif ($from == 'resource') {
                $info['is_verified'] = $avatar->is_verified;
                $info['partners'] = $avatar->partners->count();
            }
            return $info;
        }
        return null;
    }

    /**
     * Facebook Registration
     * @param $info
     * @return Profile
     */
    public function registerFacebook($info)
    {
        $profile = new Profile();
        $profile->fb_id = $info['fb_id'];
        $profile->name = $info['fb_name'];
        $profile->email = $info['fb_email'] != 'undefined' ? $info['fb_email'] : null;
        if ($profile->email != null) {
            $profile->email_verified = 1;
        }
        $profile->gender = isset($info['fb_gender']) ? $info['fb_gender'] : '';
        $profile->pro_pic = $info['fb_picture'];
        $profile->remember_token = str_random(255);
        $profile->save();
        return $profile;
    }

    public function uploadImage($profile, $photo, $folder, $extension = ".jpg")
    {
        $filename = $profile->id . '_profile_image' . $extension;
        $s3 = Storage::disk('s3');
        $s3->put($folder . $filename, file_get_contents($photo), 'public');
        return env('S3_URL') . $folder . $filename;
    }

    /**
     * Avatar registration by Facebook
     * @param $avatar
     * @param $request
     * @param $user
     * @return mixed
     */
    public function registerAvatarByFacebook($avatar, $request, $user)
    {
        if ($avatar == 'customer') {
            $customer = Customer::create([
                'remember_token' => str_random(255),
                'profile_id' => $user->id
            ]);
            $customer = Customer::find($customer->id);
            $referral_creator = new ReferralCreator($customer);
            $referral_creator->create();
            if ($request->has('referral_code')) {
                $this->updateCustomerOwnVoucherNReferral($customer, $request->referral_code);
            }
        } elseif ($avatar == 'resource') {
            $resource = new Resource();
            $resource->profile_id = $user->id;
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
        $promo = new Promotion();
        $promo->customer_id = $customer->id;
        $promo->voucher_id = $voucher->id;
        $promo->is_valid = 1;
        $date = Carbon::now()->addDays(constants('REFERRAL_VALID_DAYS'));
        $promo->valid_till = $date->toDateString() . " 23:59:59";
        return $promo->save();
    }

    /**
     * Mobile Registration
     * @param $info
     * @return mixed
     */
    public function registerMobile($info)
    {
        $profile = Profile::create([
            'mobile' => $info['mobile'],
            'mobile_verified' => 1,
            "remember_token" => str_random(255)
        ]);
        return Profile::find($profile->id);
    }


    /**
     * Avatar registration by Kit
     * @param $avatar
     * @param $request
     * @param $user
     * @return mixed
     */
    public function registerAvatarByKit($avatar, $request, $user)
    {
        if ($avatar == 'customer') {
            $customer = Customer::create([
                'remember_token' => str_random(255),
                'profile_id' => $user->id
            ]);
            $customer = Customer::find($customer->id);
            $referral_creator = new ReferralCreator($customer);
            $referral_creator->create();
            if ($request->has('referral_code')) {
                $this->updateCustomerOwnVoucherNReferral($customer, $request->referral_code);
            }
        } elseif ($avatar == 'resource') {
            $resource = new Resource();
            $resource->profile_id = $user->id;
            $resource->remember_token = str_random(255);
            $resource->save();
            return $resource;
        } elseif ($avatar == env('AFFILIATE_AVATAR_NAME')) {
            $affiliate = new Affiliate();
            $affiliate->profile_id = $user->id;
            $affiliate->remember_token = str_random(255);
            $affiliate->banking_info = json_encode(array('bKash' => ''));
            $affiliate->save();
            (new NotificationRepository())->forAffiliateRegistration($affiliate);
        }
    }

    /**
     * Avatar registration By Email
     * @param $avatar
     * @param $request
     * @param $user
     * @return mixed
     */
    public function registerAvatarByEmail($avatar, $request, $user)
    {
        if ($avatar == 'customer') {
            $customer = Customer::create([
                'remember_token' => str_random(255),
                'profile_id' => $user->id
            ]);
            $customer = Customer::find($customer->id);
            $referral_creator = new ReferralCreator($customer);
            $referral_creator->create();
            if ($request->has('referral_code')) {
                $this->updateCustomerOwnVoucherNReferral($customer, $request->referral_code);
            }
        } elseif ($avatar == 'resource') {
            $resource = new Resource();
            $resource->profile_id = $user->id;
            $resource->remember_token = str_random(255);
            $resource->save();
        } elseif ($avatar == 'member') {
            $member = new Member();
            $member->remember_token = str_random(255);
            $member->profile_id = $user->id;
            $member->save();
            return $member;
        }
    }

    public function getAvatar($from)
    {
        return constants('AVATAR')[$from];
    }

}