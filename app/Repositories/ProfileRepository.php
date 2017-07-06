<?php

namespace App\Repositories;

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

    public function getProfileInfo($from, Profile $profile)
    {
        if ($from == env('SHEBA_RESOURCE_APP')) {
            $resource = $profile->resource;
            if ($resource != null) {
                $info = array(
                    'id' => $resource->id,
                    'token' => $resource->remember_token
                );
                return $info;
            }
        } elseif ($from == env('SHEBA_CUSTOMER_APP')) {
            $customer = $profile->customer;
            if ($customer != null) {
                $info = array(
                    'id' => $customer->id,
                    'token' => $customer->remember_token
                );
                return $info;
            }
        }
        return false;
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
        $profile->email = $info['fb_email'];
        $profile->gender = isset($info['fb_gender']) ? $info['fb_gender'] : '';
        $profile->pro_pic = $info['fb_picture'];
        $profile->email_verified = 1;
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
                'fb_id' => $user->fb_id,
                'name' => $user->name,
                'email' => $user->email,
                'gender' => $user->gender,
                'pro_pic' => $user->pro_pic,
                'email_verified' => 1,
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
            $resource->name = $user->name;
            $resource->profile_image = $user->pro_pic;
            $resource->email = $user->email;
            $resource->profile_id = $user->id;
            $resource->remember_token = str_random(255);
            $resource->save();
            return $resource;
        }
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
        return Profile::create([
            'mobile' => $info['mobile'],
            'mobile_verified' => 1,
            "remember_token" => str_random(255)
        ]);
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
                'mobile' => $user->mobile,
                'mobile_verified' => 1,
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
            $resource->contact_no = $user->mobile;
            $resource->is_verified = 1;
            $resource->profile_id = $user->id;
            $resource->remember_token = str_random(255);
            $resource->save();
            return $resource;
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
                'email' => $user->email,
                'password' => $user->password,
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
            $resource->email = $user->email;
            $resource->password = $user->password;
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

}