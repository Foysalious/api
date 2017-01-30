<?php
namespace App\Repositories;

use App\Models\Customer;
use App\Models\CustomerMobile;
use Cache;
use Mail;
use Carbon\Carbon;
use Hash;
use JWTAuth;
use Redis;

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
        if ($customer = Customer::where("$queryColumn", $data)->first()) {
            return $customer;
        }
        return false;
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
        return Customer::create([
            'mobile' => $info['mobile'],
            'mobile_verified' => 1,
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
            "remember_token" => str_random(60),
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
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($customer->email)->subject('Email Verification');

        });
//        $expired_at = Carbon::now()->addMinutes(30);
//        Cache::put($customer->id . '-verification-email', $verfication_code, $expired_at);
    }

    /**
     * Send password reset link to a customer's email
     * @param $customer
     */
    public function sendResetPasswordEmail($customer)
    {
        $verfication_code = str_random(60);
        Mail::send('emails.reset-password', ['customer' => $customer, 'code' => $verfication_code], function ($m) use ($customer) {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
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
        $customer = new Customer();
        $customer->fb_id = $info['id'];
        $customer->name = $info['name'];
        $customer->email = $info['email'];
        $customer->gender = isset($info['gender']) ? $info['gender'] : '';
        $customer->pro_pic = $info['picture']['data']['url'];
        $customer->email_verified = 1;
        $customer->reference_code = str_random(6);
        $customer->remember_token = str_random(60);
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
        if (isset($info['id'])) {
            $customer->fb_id = $info['id'];
        }
        $customer->name = $info['name'];
        if (!isset($info['email'])) {
            $customer->email = $info['email'];
            $customer->email_verified = 1;
        }
        $customer->gender = $info['gender'];
        $customer->pro_pic = $info['picture']['data']['url'];
        $customer->update();
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
        $customer = Customer::find($order_info['customer_id']);
        $customer_update_data = [];

        if (empty($customer->name) || $customer->name == ""){
            $customer_update_data['name'] = $order_info['name'];
        }

        if (empty($customer->mobile)){
            $customer_update_data['mobile'] = $order_info['phone'];
        }

        if (!empty($customer_update_data)){
            $customer->update($customer_update_data);
        }
    }

}