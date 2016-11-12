<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\FacebookAccountKit;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use JWTAuth;
use JWTFactory;
use App\Customer;
use Session;

class AuthController extends Controller {
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    private $fbKit;
    private $customer;

    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->customer = new CustomerRepository();
    }

    /**
     * Login method for Customer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        /*
        * Remember me
        */
        If ($request->has('remember_token'))
        {
            $customer = Customer::where('remember_token', $request->get('remember_token'))->first();
            //remember_token is valid for a customer
            if ($customer)
            {
                //create token for that user
                $token = JWTAuth::fromUser($customer);
                // all good so return the token
                return response()->json(compact('token'));
            }
        }

        /*
         *
         * Normal login for customer
         *
         */
        $credentials = $request->only('email', 'password');

        try
        {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials))
            {
                return response()->json(['msg' => 'invalid_credentials', 'code' => '401'], 401);
            }
        }
        catch (JWTException $e)
        {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        //get the customer & assign a remember me token
        $customer = Customer::where('email', $request->input('email'))->first();
        $customer->remember_token = str_random(60);
        $customer->update();
        // all good so return the token
        return response()->json(['msg' => 'Login successful', 'code' => '200', 'token' => $token, 'remember_token' => $customer->remember_token]);
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
            return response()->json(['message' => 'number already exists', 'code' => '409']);
        }
        //register the customer with verified mobile
        $customer = $this->customer->registerMobile($code_data['mobile']);
        //generate token based on customer
        $token = JWTAuth::fromUser($customer);
        // return success with token
        return response()->json(['msg' => 'successful', 'code' => '200', 'token' => $token, 'remember_token' => $customer->remember_token]);
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
            return response()->json(['msg' => 'email already exists', 'code' => '409']);
        }
        $customer = $this->customer->registerEmailPassword($request->input('email'), $request->input('password'));

        $this->customer->sendVerificationMail($customer);
        //generate token based on customer
        $token = JWTAuth::fromUser($customer);
        // return success with token
        return response()->json(['msg' => 'Register with email successful', 'code' => '200', 'token' => $token, 'remember_token' => $customer->remember_token]);
    }


    public function loginWithKit(Request $request)
    {
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        //login the customer if corresponding mobile exists
        if ($customer = $this->customer->ifExist($code_data['mobile'], 'mobile'))
        {
            //generate remember_token for customer
            $remember_token = str_random(60);
            $customer->remember_token = $remember_token;
            $customer->update();
            //generate token based on customer
            $token = JWTAuth::fromUser($customer);
            // return success with token
            return response()->json(['msg' => 'Login with mobile successful', 'code' => '200', 'token' => $token, 'remember_token' => $customer->remember_token]);
        }
        else
        {
            return response()->json(['msg' => 'mobile doesn\'t exist', 'code' => '404']);
        }

    }


}
