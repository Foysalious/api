<?php
namespace App\Repositories;

use App\Customer;
use Cache;
use Mail;
use Carbon\Carbon;

class CustomerRepository {


    /**
     * Check if Customer already exists
     * @param $data
     * @param $queryColumn
     * @return bool
     */
    public function ifExist($data, $queryColumn)
    {
        if (Customer::where("$queryColumn", $data)->first())
        {
            return true;
        }
        return false;
    }


    /**
     * Register Customer with mobile
     * @param $mobile
     * @return static
     */
    public function registerMobile($mobile)
    {
        return Customer::create([
            'mobile' => $mobile,
            "remember_token" => str_random(60),
            'mobile_verifired' => 1
        ]);
    }

    /**
     * Register Customer with email & password
     * @param $email
     * @param $password
     * @return static
     */
    public function registerEmailPassword($email, $password)
    {
        return Customer::create([
            "email" => $email,
            "remember_token" => str_random(60),
            "password" => bcrypt($password)
        ]);
    }


    /**
     * Send verification email after a successful customer registration
     * @param $customer
     */
    public function sendVerificationMail($customer)
    {
        $verfication_code = str_random(60);
        Mail::send('emails.verification', ['customer' => $customer, 'code' => $verfication_code], function ($m) use ($customer)
        {
            $m->from('yourEmail@domain.com', 'Sheba.xyz');
            $m->to($customer->email)->subject('Email Verification');

        });
        $expired_at = Carbon::now()->addMinutes(30);
        Cache::put("$customer->id", $verfication_code, $expired_at);
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


}