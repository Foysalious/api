<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\FacebookAccountKit;
use App\Models\Resource;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use App\Http\Controllers\Controller;
use JWTAuth;
use JWTFactory;
use App\Models\Customer;
use Session;
use Redis;

class LoginController extends Controller
{
    private $fbKit;
    private $customer;

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
        If ($request->has('remember_token')) {
            $customer = Customer::where('remember_token', $request->input('remember_token'))->first();
            //remember_token is valid for a customer
            if ($customer) {
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
        try {
            // verify email credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                //email verification failed. Now check for mobile verification
                if (!$mobileToken = $this->customer->attemptByMobile($credentials)) {
                    return response()->json(['msg' => 'invalid_credentials', 'code' => 404]);
                }
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        //get the customer

        // when email is provided for login
        if ($token) {
            $customer = Customer::where('email', $request->input('email'))->first();
        } // when mobile is provided for login
        else {
            $customer = Customer::where('mobile', $request->input('email'))->first();
            $token = $mobileToken;
        }
        // all good so return the token
        return response()->json([
            'msg' => 'Login successful', 'code' => 200, 'token' => $token, 'remember_token' => $customer->remember_token,
            'customer' => $customer->id, 'customer_img' => $customer->pro_pic
        ]);
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

}