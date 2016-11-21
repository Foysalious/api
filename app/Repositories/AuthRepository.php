<?php


namespace App\Repositories;

use App\Models\Customer;
use JWTAuth;

class AuthRepository {

    public function checkValidCustomer($remember_me, $token)
    {
        $customer = Customer::where('remember_token', $remember_me)->first();
        //remember_token is valid for a customer
        if (!$customer)
        {
            if (!$customer = JWTAuth::authenticate($token))
            {
                return false;
            }
        }
        return $customer;
    }
}