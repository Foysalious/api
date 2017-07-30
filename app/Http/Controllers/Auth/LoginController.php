<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\FacebookAccountKit;
use App\Models\Profile;
use App\Models\Resource;
use App\Repositories\CustomerRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use App\Http\Controllers\Controller;
use JWTAuth;
use JWTFactory;
use App\Models\Customer;
use Session;
use Redis;
use Hash;

class LoginController extends Controller
{
    private $fbKit;
    private $customer;
    private $profileRepository;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->customer = new CustomerRepository();
        $this->profileRepository = new ProfileRepository();
    }

    /**
     * Login method for Customer
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
//    public function login(Request $request)
//    {
//        /*
//        * Remember me
//        */
//        If ($request->has('remember_token')) {
//            $customer = Customer::where('remember_token', $request->input('remember_token'))->first();
//            //remember_token is valid for a customer
//            if ($customer) {
//                //create token for that user
//                $token = JWTAuth::fromUser($customer);
//                // all good so return the token
//                return response()->json(compact('token'));
//            }
//        }
//
//        /*
//         *
//         * Normal login for customer
//         *
//         */
//        $credentials = $request->only('email', 'password');
//        try {
//            // verify email credentials and create a token for the user
//            if (!$token = JWTAuth::attempt($credentials)) {
//                //email verification failed. Now check for mobile verification
//                if (!$mobileToken = $this->customer->attemptByMobile($credentials)) {
//                    return response()->json(['msg' => 'invalid_credentials', 'code' => 404]);
//                }
//            }
//        } catch (JWTException $e) {
//            // something went wrong whilst attempting to encode the token
//            return response()->json(['error' => 'could_not_create_token'], 500);
//        }
//        //get the customer
//
//        // when email is provided for login
//        if ($token) {
//            $customer = Customer::where('email', $request->input('email'))->first();
//        } // when mobile is provided for login
//        else {
//            $customer = Customer::where('mobile', $request->input('email'))->first();
//            $token = $mobileToken;
//        }
//        // all good so return the token
//        return response()->json([
//            'msg' => 'Login successful', 'code' => 200, 'token' => $token, 'remember_token' => $customer->remember_token,
//            'customer' => $customer->id, 'customer_img' => $customer->pro_pic
//        ]);
//    }

    public function login(Request $request)
    {
        if ($msg = $this->_validateLoginRequest($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $profile = $this->profileRepository->ifExist($request->email, 'email');
        if ($profile == false) {
            $profile = $this->profileRepository->ifExist($this->formatMobile($request->email), 'mobile');
        }
        if ($profile != false) {
            if (Hash::check($request->input('password'), $profile->password)) {
                $info = $this->profileRepository->getProfileInfo($this->profileRepository->getAvatar($request->from), $profile);
                if ($info != false) {
                    return response()->json(['code' => 200, 'info' => $info]);
                }
            }
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    private function formatMobile($mobile)
    {
        // mobile starts with '+88'
        if (preg_match("/^(\+88)/", $mobile)) {
            return $mobile;
        } // when mobile starts with '88' replace it with '+880'
        elseif (preg_match("/^(88)/", $mobile)) {
            return preg_replace('/^88/', '+88', $mobile);
        } // real mobile no add '+880' at the start
        else {
            return '+88' . $mobile;
        }
    }

    /**
     * Customer login with facebook kit
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginWithKit(Request $request)
    {
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        //login the customer if corresponding mobile exists
        if ($customer = $this->customer->ifExist($code_data['mobile'], 'mobile')) {
            //generate token based on customer
            $token = JWTAuth::fromUser($customer);
            // return success with token
            return response()->json([
                'msg' => 'Login with mobile successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
            ]);
        } else {
            return response()->json(['msg' => 'mobile doesn\'t exist', 'code' => 404]);
        }

    }

    public function checkForAuthentication(Request $request)
    {
        $key = Redis::get($request->input('access_token'));
        //key exists
        if ($key != null) {
            $info = json_decode($key);
            if ($info->avatar == 'customer') {
                $customer = Customer::find($info->id);
                $token = JWTAuth::fromUser($customer);
                Redis::del($request->input('access_token'));
                if ($customer->profile_id == $info->profile_id) {
                    return response()->json([
                        'msg' => 'successful', 'code' => 200, 'token' => $token,
                        'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
                    ]);
                }
            } else if ($info->avatar == 'resource') {
                $resource = Resource::find($info->id);
                Redis::del($request->input('access_token'));
                if ($resource->profile_id == $info->profile_id) {
                    return response()->json([
                        'msg' => 'successful', 'code' => 200, 'resource' => $resource->id
                    ]);
                }
            }
        } else {
            return response()->json(['msg' => 'not found', 'code' => 404]);
        }
    }

    private function _validateLoginRequest($request)
    {
        $from = implode(',', constants('FROM'));
        $validator = Validator::make($request->all(), [
            'from' => "required|in:$from",
            'email' => 'required|email',
            'password' => 'required'
        ], ['in' => 'from value is invalid!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

}