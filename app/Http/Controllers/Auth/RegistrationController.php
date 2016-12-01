<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Customer;
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
        if ($customer = $this->customer->ifExist($code_data['mobile'], 'mobile'))
        {
            $token = JWTAuth::fromUser($customer);
            // return success with token
            return response()->json([
                'msg' => 'successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id
            ]);
        }
        array_add($request, 'mobile', $code_data['mobile']);
        //register the customer with verified mobile
        $customer = $this->customer->registerMobile($request->all());
        //generate token based on customer
        $token = JWTAuth::fromUser($customer);
        // return success with token
        return response()->json([
            'msg' => 'successful', 'code' => 200, 'token' => $token,
            'remember_token' => $customer->remember_token, 'customer' => $customer->id
        ]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithEmail(Request $request)
    {
        //return error if customer already exists
        if ($customer = $this->customer->ifExist($request->input('email'), 'email'))
        {
            $token = JWTAuth::fromUser($customer);
            // return success with token
            return response()->json([
                'msg' => 'successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id
            ]);
        }
        $customer = $this->customer->registerEmailPassword($request->all());

        $this->customer->sendVerificationMail($customer);
        //generate token based on customer
        $token = JWTAuth::fromUser($customer);
        // return success with token
        return response()->json([
            'msg' => 'Register with email successful', 'code' => 200, 'token' => $token,
            'remember_token' => $customer->remember_token, 'customer' => $customer->id
        ]);
    }

    public function registerWithFacebook(Request $request)
    {
        $customer = $this->customer->ifExist($request->get('id'), 'fb_id');
        //fb_id doesn't exist
        if (!$customer)
        {
            //email already exist for this facebook user now logged in the user
            if ($customer = $this->customer->ifExist($request->get('email'), 'email'))
            {
                $token = JWTAuth::fromUser($customer);
                $customer->reference_code = str_random(30);
                $customer->remember_token = str_random(60);
                $customer->update();
                // return success with token
                return response()->json([
                    'msg' => 'successful', 'code' => 200, 'token' => $token,
                    'remember_token' => $customer->remember_token, 'customer' => $customer->id
                ]);
            }
            else
            {
                $customer = $this->customer->registerFacebook($request->all());
                $token = JWTAuth::fromUser($customer);
                // return success with token
                return response()->json([
                    'msg' => 'successful', 'code' => 200, 'token' => $token,
                    'remember_token' => $customer->remember_token, 'customer' => $customer->id
                ]);
            }
        }
        //fb_id exist so logged in the user
        else
        {
            $customer = $this->customer->updateCustomerInfo($customer, $request->all());
            $token = JWTAuth::fromUser($customer);
            // return success with token
            return response()->json([
                'msg' => 'successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id
            ]);
        }
    }

}