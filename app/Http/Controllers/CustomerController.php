<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Cache;
use App\Http\Requests;

class CustomerController extends Controller {
    protected $customer;

    public function __construct()
    {
        $this->customer = new CustomerRepository();
    }

    /**
     * Verify a customer's email with email verification code
     * @param Customer $customer
     * @param $code
     * @return string
     */
    public function emailVerification(Customer $customer, $code)
    {
        $verification_code = Cache::get($customer->id . '-verification-email');
        if (empty($verification_code))
        {
            Cache::forget($customer->id . '-verification-email');
            return "Code has expired";
        }
        elseif ($verification_code == $code)
        {
            $message = $this->customer->emailVerified($customer);
            Cache::forget($customer->id . '-verification-email');
            return $message;
        }
        else
        {
            return "Verification code doesn't match!";
        }
    }
}
