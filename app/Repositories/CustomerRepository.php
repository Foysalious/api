<?php
namespace App\Repositories;

use App\Models\Customer;
use Cache;
use Mail;
use Carbon\Carbon;
use Hash;
use JWTAuth;

class CustomerRepository {


    /**
     * Check if Customer already exists
     * @param $data
     * @param $queryColumn
     * @return bool
     */
    public function ifExist($data, $queryColumn)
    {
        if ($customer = Customer::where("$queryColumn", $data)->first())
        {
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
        if (isset($info['reference_code']))
        {
            //if reference_code is valid then register with referrer_id
            if ($customer = $this->ifExist($info['reference_code'], 'reference_code'))
            {
                dd($customer);
                return Customer::create([
                    'mobile' => $info['mobile'],
                    'mobile_verified' => 1,
                    "remember_token" => str_random(60),
                    "reference_code" => str_random(30),
                    "referrer_id" => $customer->id
                ]);
            }
        }
        return Customer::create([
            'mobile' => $info['mobile'],
            'mobile_verified' => 1,
            "remember_token" => str_random(60),
            "reference_code" => str_random(30)
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
        if (isset($info['reference_code']))
        {
            //if reference_code is valid then register with referrer_id
            if ($customer = $this->ifExist($info['reference_code'], 'reference_code'))
            {
                return Customer::create([
                    "email" => $info['email'],
                    "password" => bcrypt($info['password']),
                    "remember_token" => str_random(60),
                    "reference_code" => str_random(30),
                    "referrer_id" => $customer->id
                ]);
            }
        }
        return Customer::create([
            "email" => $info['email'],
            "password" => bcrypt($info['password']),
            "remember_token" => str_random(60),
            "reference_code" => str_random(30)
        ]);
    }


    /**
     * Send verification email after a successful customer registration
     * @param $customer
     */
    public function sendVerificationMail($customer)
    {
        $verfication_code = str_random(60);
        Mail::send('emails.email-verification', ['customer' => $customer, 'code' => $verfication_code], function ($m) use ($customer)
        {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($customer->email)->subject('Email Verification');

        });
        $expired_at = Carbon::now()->addMinutes(30);
        Cache::put($customer->id . '-verification-email', $verfication_code, $expired_at);
    }

    /**
     * Send password reset link to a customer's email
     * @param $customer
     */
    public function sendResetPasswordEmail($customer)
    {
        $verfication_code = str_random(60);
        Mail::send('emails.reset-password', ['customer' => $customer, 'code' => $verfication_code], function ($m) use ($customer)
        {
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
        if ($customer->save())
        {
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
        if ($customer)
        {
            //verified
            if (Hash::check($credentials['password'], $customer->password))
            {
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
        $customer->reference_code = str_random(30);
        $customer->remember_token = str_random(60);
        //reference_code is send through request
        if (isset($info['reference_code']))
        {
            //if reference_code is valid then register with referrer_id
            if ($referredCustomer = $this->ifExist($info['reference_code'], 'reference_code'))
            {
                $customer->referrer_id = $referredCustomer->id;
            }
        }
        $customer->save();
        return $customer;
    }

    public function updateCustomerInfo($customer, $info)
    {
        $customer->name = $info['name'];
        $customer->email = $info['email'];
        $customer->pro_pic = $info['picture']['data']['url'];
        $customer->update();
    }

}