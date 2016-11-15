<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Session;

class RegistrationController extends Controller {
    private $fbKit;
    private $customer;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->customer = new CustomerRepository();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithMobile(Request $request)
    {
        //Authenticate the code with facebook kit
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        //return error if customer already exists
        if ($this->customer->ifExist($code_data['mobile'], 'mobile'))
        {
            return response()->json(['message' => 'number already exists', 'code' => 409]);
        }
        //register the customer with verified mobile
        $customer = $this->customer->registerMobile($code_data['mobile']);
        //generate token based on customer
        $token = JWTAuth::fromUser($customer);
        // return success with token
        return response()->json(['msg' => 'successful', 'code' => 200, 'token' => $token, 'remember_token' => $customer->remember_token]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithEmail(Request $request)
    {
        //return error if customer already exists
        if ($this->customer->ifExist($request->input('email'), 'email'))
        {
            return response()->json(['msg' => 'email already exists', 'code' => 409]);
        }
        $customer = $this->customer->registerEmailPassword($request->input('email'), $request->input('password'));

        $this->customer->sendVerificationMail($customer);
        //generate token based on customer
        $token = JWTAuth::fromUser($customer);
        // return success with token
        return response()->json(['msg' => 'Register with email successful', 'code' => 200, 'token' => $token, 'remember_token' => $customer->remember_token]);
    }

}