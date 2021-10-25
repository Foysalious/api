<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\CustomerMobile;
use App\Models\Order;
use App\Models\Profile;
use Cache;
use Mail;
use Carbon\Carbon;
use Hash;
use JWTAuth;
use Illuminate\Support\Facades\Redis;

class CustomerRepository
{


    /**
     * Check if Customer already exists
     * @param $data
     * @param $queryColumn
     * @return bool
     */
    public function ifExist($data, $queryColumn)
    {
        $customer = Customer::where($queryColumn, $data)->first();
        if ($customer != null) {
            return $customer;
        } else {
            return false;
        }
    }


    /**
     * Register Customer with mobile
     * @param $mobile
     * @return static
     */
    public function registerMobile($info)
    {
        //reference_code is send through request
        if (isset($info['reference_code'])) {
            //if reference_code is valid then register with referrer_id
            if ($customer = $this->ifExist($info['reference_code'], 'reference_code')) {
                return Customer::create([
                    'mobile' => $info['mobile'],
                    'mobile_verified' => 1,
                    "remember_token" => str_random(60),
                    "reference_code" => str_random(6),
                    "referrer_id" => $customer->id
                ]);
            }
        }
        $profile = Profile::create([
            'mobile' => $info['mobile'],
            'mobile_verified' => 1,
            "remember_token" => str_random(255)
        ]);
        return Customer::create([
            'mobile' => $info['mobile'],
            'mobile_verified' => 1,
            'profile_id' => $profile->id,
            "remember_token" => str_random(60),
            "reference_code" => str_random(6)
        ]);
    }


    /**
     * Register Customer with email & password
     * @param $info
     * @return static
     */
    public function registerEmailPassword($info)
    {
        //reference_code is send through request
        if (isset($info['reference_code'])) {
            //if reference_code is valid then register with referrer_id
            if ($customer = $this->ifExist($info['reference_code'], 'reference_code')) {
                return Customer::create([
                    "email" => $info['email'],
                    "password" => bcrypt($info['password']),
                    "remember_token" => str_random(60),
                    "reference_code" => str_random(6),
                    "referrer_id" => $customer->id
                ]);
            }
        }
        return Customer::create([
            "email" => $info['email'],
            "password" => bcrypt($info['password']),
            "remember_token" => str_random(255),
            "reference_code" => str_random(6)
        ]);
    }


    /**
     * Send verification email after a successful customer registration
     * @param $customer
     */
    public function sendVerificationMail($customer)
    {
        $verfication_code = str_random(30);
        Redis::set('email_verification-' . $customer->id, $verfication_code);
        Redis::expire('email_verification-' . $customer->id, 30 * 60);
        Mail::send('emails.email-verification', ['customer' => $customer, 'code' => $verfication_code], function ($m) use ($customer) {
            $m->to($customer->email)->subject('Email Verification');

        });
    }

    /**
     * Send password reset link to a customer's email
     * @param $customer
     */
    public function sendResetPasswordEmail($customer)
    {
        $verfication_code = str_random(60);
        Mail::send('emails.reset-password', ['customer' => $customer, 'code' => $verfication_code], function ($m) use ($customer) {
            $m->to($customer->email)->subject('Reset Password');

        });
        $expired_at = Carbon::now()->addMinutes(30);
        Cache::put('$customer->id' . '-reset-password', $verfication_code, $expired_at);
    }

    /**
     * Verify a customer's email
     * @param $customer
     * @return string
     */
    public function emailVerified($customer)
    {
        $customer->email_verified = 1;
        if ($customer->save()) {
            return "$customer->email is now verified";
        }
        return "something went wrong";
    }

    /**
     * Match customer with their mobile & password
     * @param $credentials
     * @return bool
     */
    public function attemptByMobile($credentials)
    {
        $customer = Customer::where('mobile', $credentials['email'])->first();
        if ($customer) {
            //verified
            if (Hash::check($credentials['password'], $customer->password)) {
                return JWTAuth::fromUser($customer);
            }
            return false;
        }
        return false;
    }

    public function registerFacebook($info)
    {
        $profile = new Profile();
        $profile->fb_id = $info['id'];
        $profile->name = $info['name'];
        $profile->email = $info['email'];
        $profile->gender = isset($info['gender']) ? $info['gender'] : '';
        $profile->pro_pic = $info['picture']['data']['url'];
        $profile->email_verified = 1;
        $profile->remember_token = str_random(255);
        $profile->save();

        $customer = new Customer();
        $customer->fb_id = $info['id'];
        $customer->name = $info['name'];
        $customer->email = $info['email'];
        $customer->gender = isset($info['gender']) ? $info['gender'] : '';
        $customer->pro_pic = $info['picture']['data']['url'];
        $customer->email_verified = 1;
        $customer->profile_id = $profile->id;
        $customer->reference_code = str_random(6);
        $customer->remember_token = str_random(255);
        //reference_code is send through request
        if (isset($info['reference_code'])) {
            //if reference_code is valid then register with referrer_id
            if ($referredCustomer = $this->ifExist($info['reference_code'], 'reference_code')) {
                $customer->referrer_id = $referredCustomer->id;
            }
        }
        $customer->save();
        return $customer;
    }

    public function updateCustomerInfo($customer, $info)
    {
//        $customer->name = $info['name'];
//        if (!isset($info['email'])) {
//            $customer->email = $info['email'];
//            $customer->email_verified = 1;
//        }
//        $customer->gender = $info['gender'];
//        $customer->pro_pic = $info['picture']['data']['url'];
        $customer->fb_id = $info['id'];
        if ($customer->update()) {
            $profile = $customer->profile;
            $profile->fb_id = $info['id'];
            $profile->update();
        }
        return $customer;
    }

    public function mobileValid($mobile)
    {
        if ($customer_mobile = CustomerMobile::where('mobile', $mobile)->first()) {
            return false;
        }
        return true;
    }

    public function addCustomerMobile($customer)
    {
        $customer_mobile = new CustomerMobile();
        $customer_mobile->mobile = $customer->mobile;
        $customer_mobile->customer_id = $customer->id;
        $customer_mobile->save();
    }

    public function updateCustomerNameIfEmptyWhenPlacingOrder($order_info)
    {
        $update = false;
        $profile = Customer::find($order_info['customer_id'])->profile;
        $this->updateProfileNameIfEmpty($profile, $order_info['name'], $update);
        $this->updateProfileMobileIfEmpty($profile, formatMobile($order_info['phone']), $update);
        if ($update) $profile->update();
    }

    private function updateProfileNameIfEmpty(&$profile, $name, &$update)
    {
        if (empty($profile->name)) {
            $profile->name = $name;
            $update = true;
        }
    }

    private function updateProfileMobileIfEmpty(&$profile, $mobile, &$update)
    {
        $mobile_profile = Profile::where('mobile', $mobile)->first();
        if (empty($profile->mobile) && !$mobile_profile) {
            $profile->mobile = $mobile;
            $update = true;
        }
    }

    public function updateProfileInfoWhilePlacingOrder(Order $order)
    {
        try {
            $update = false;
            $profile = $order->customer->profile;
            $this->updateProfileNameIfEmpty($profile, $order->delivery_name, $update);
            $this->updateProfileMobileIfEmpty($profile, formatMobile($order->delivery_mobile), $update);
            if ($update) return $profile->update();
        } catch (\Throwable $e) {
            return false;
        }
    }

}
