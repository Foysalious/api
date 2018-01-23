<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Jobs\SendEmailVerficationEmail;
use App\Models\Customer;
use App\Models\CustomerMobile;
use App\Models\Profile;
use App\Repositories\CustomerRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use JWTAuth;
use JWTFactory;
use Session;

class RegistrationController extends Controller
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

    public function registerByEmailAndMobile(Request $request)
    {
        try {
            $from = implode(',', constants('FROM'));
            $this->validate($request, ['email' => 'required|email|unique:profiles', 'password' => 'required|min:6', 'kit_code' => 'required', 'from' => "required|in:$from"]);
            if ($kit_data = $this->fbKit->authenticateKit($request->kit_code)) {
                $from = $this->profileRepository->getAvatar($request->from);
                $profile = $this->profileRepository->ifExist(formatMobile($kit_data['mobile']), 'mobile');
                if (!$profile) {
                    $profile = $this->profileRepository->store(array_merge($request->all(), ['mobile' => $kit_data['mobile'], 'mobile_verified' => 1]));
                } else {
                    $this->profileRepository->update($profile, ['email' => $request->email]);
                }
                $this->profileRepository->registerAvatar($from, $request, $profile);
                if ($profile->$from) {
                    $this->profileRepository->registerAvatar($from, $request, $profile);
                }
                $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
                return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
            }
            return api_response($request, null, 403);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithMobile(Request $request)
    {
        //Authenticate the code with facebook kit
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        //login if customer already exists
        if ($customer = Customer::where('mobile', $code_data['mobile'])->first()) {
            $token = JWTAuth::fromUser($customer);
            // return success with token
            return response()->json([
                'msg' => 'successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
            ]);
        } //return error if mobile already exists as secondary
        elseif ($customer_mobile = CustomerMobile::where('mobile', $code_data['mobile'])->first()) {
            return response()->json(['msg' => 'uses as secondary number', 'code' => 409]);
        } else {
            array_add($request, 'mobile', $code_data['mobile']);
            //register the customer with verified mobile
            $customer = $this->customer->registerMobile($request->all());
            $this->customer->addCustomerMobile($customer);
            //generate token based on customer
            $token = JWTAuth::fromUser($customer);
            $customer = Customer::find($customer->id);
            // return success with token
            return response()->json([
                'msg' => 'successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
            ]);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerWithEmail(Request $request)
    {
        //return customer if customer already exists
        if ($customer = $this->customer->ifExist($request->input('email'), 'email')) {
            // return error
            return response()->json([
                'msg' => 'account already exists for this email', 'code' => 409
            ]);
        }
        $customer = $this->customer->registerEmailPassword($request->all());

        $this->dispatch(new SendEmailVerficationEmail($customer));
        //generate token based on customer
        $token = JWTAuth::fromUser($customer);
        $customer = Customer::find($customer->id);
        // return success with token
        return response()->json([
            'msg' => 'Register with email successful', 'code' => 200, 'token' => $token,
            'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
        ]);
    }

    public function registerWithFacebook(Request $request)
    {
        $customer = $this->customer->ifExist($request->input('id'), 'fb_id');
        //fb_id doesn't exist
        if (!$customer) {
            //email already exist for this facebook user so logged in the user
            if ($customer = $this->customer->ifExist($request->input('email'), 'email')) {
                $customer = $this->customer->updateCustomerInfo($customer, $request->all());
                $token = JWTAuth::fromUser($customer);
            } else {
                $customer = $this->customer->registerFacebook($request->all());
                $token = JWTAuth::fromUser($customer);
            }
        } //fb_id exist so logged in the user
        else {
//            $customer = $this->customer->updateCustomerInfo($customer, $request->all());
            $token = JWTAuth::fromUser($customer);
        }
        // return success with token
        return response()->json([
            'msg' => 'successful', 'code' => 200, 'token' => $token, 'remember_token' => $customer->remember_token,
            'customer' => $customer->id, 'customer_img' => $customer->pro_pic
        ]);
    }

    public function register(Request $request)
    {
        if ($msg = $this->_validateRegistration($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $profile = $this->profileRepository->ifExist($request->email, 'email');
        if ($profile == false) {
            $profile = $this->profileRepository->registerEmail($request);
            $avatar = $this->profileRepository->getAvatar($request->from);
            $this->profileRepository->registerAvatarByEmail($avatar, $request, $profile);
            $info = $this->profileRepository->getProfileInfo($avatar, $profile);
            if ($info != false) {
                return response()->json(['code' => 200, 'info' => $info]);
            }
        }
        return response()->json(['code' => 409, 'msg' => 'Already registered!']);
    }

    private function _validateRegistration($request)
    {
        $from = implode(',', constants('FROM'));
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:profiles',
            'password' => 'required|min:6',
            'from' => "required|in:$from",
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

}