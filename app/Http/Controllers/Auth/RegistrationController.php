<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Jobs\SendEmailVerficationEmail;
use App\Models\Customer;
use App\Models\CustomerMobile;
use App\Models\Profile;
use App\Repositories\CustomerRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Throwable;

class RegistrationController extends Controller
{
    /** @var FacebookAccountKit  */
    private $fbKit;
    /** @var CustomerRepository  */
    private $customer;
    /** @var ProfileRepository  */
    private $profileRepository;

    public function __construct(FacebookAccountKit $fb_kit, CustomerRepository $customer_repo, ProfileRepository $profile_repo)
    {
        $this->fbKit             = $fb_kit;
        $this->customer          = $customer_repo;
        $this->profileRepository = $profile_repo;
    }

    public function registerByEmailAndMobile(Request $request)
    {
        $from = implode(',', constants('FROM'));

        $this->validate($request, ['email' => 'required|email|unique:profiles', 'password' => 'required|min:6', 'kit_code' => 'required|string', 'from' => "required|in:$from"]);
        $name = trim($request->first_name . ' ' . $request->last_name);
        $kit_data = $this->fbKit->authenticateKit($request->kit_code);
        if (!$kit_data) return api_response($request, null, 403);
        $from = $this->profileRepository->getAvatar($request->from);
        $profile = $this->profileRepository->ifExist(formatMobile($kit_data['mobile']), 'mobile');
        if (!$profile) {
            $profile = $this->profileRepository->store(['mobile' => formatMobile($kit_data['mobile']), 'mobile_verified' => 1, 'name' => $name, 'email' => $request->email, 'password' => bcrypt($request->password)]);
        } else {
            return api_response($request, null, 400, ['message' => 'Mobile already exists! Please login']);
        }
        $this->profileRepository->registerAvatar($from, $request, $profile);
        $info = $this->profileRepository->getProfileInfo($from, Profile::find($profile->id), $request);
        return $info ? api_response($request, $info, 200, ['info' => $info]) : api_response($request, null, 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function registerWithMobile(Request $request)
    {
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        if ($customer = Customer::where('mobile', $code_data['mobile'])->first()) {
            $token = JWTAuth::fromUser($customer);
            return response()->json([
                'msg' => 'successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
            ]);
        } elseif ($customer_mobile = CustomerMobile::where('mobile', $code_data['mobile'])->first()) {
            return response()->json(['msg' => 'uses as secondary number', 'code' => 409]);
        } else {
            array_add($request, 'mobile', $code_data['mobile']);
            $customer = $this->customer->registerMobile($request->all());
            $this->customer->addCustomerMobile($customer);
            $token = JWTAuth::fromUser($customer);
            $customer = Customer::find($customer->id);
            return response()->json([
                'msg' => 'successful', 'code' => 200, 'token' => $token,
                'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function registerWithEmail(Request $request)
    {
        if ($customer = $this->customer->ifExist($request->input('email'), 'email')) {
            return response()->json([
                'msg' => 'account already exists for this email', 'code' => 409
            ]);
        }
        $customer = $this->customer->registerEmailPassword($request->all());

        $this->dispatch(new SendEmailVerficationEmail($customer));
        $token = JWTAuth::fromUser($customer);
        $customer = Customer::find($customer->id);
        return response()->json([
            'msg' => 'Register with email successful', 'code' => 200, 'token' => $token,
            'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->pro_pic
        ]);
    }

    public function registerWithFacebook(Request $request)
    {
        $customer = $this->customer->ifExist($request->input('id'), 'fb_id');
        if (!$customer) {
            if ($customer = $this->customer->ifExist($request->input('email'), 'email')) {
                $customer = $this->customer->updateCustomerInfo($customer, $request->all());
                $token = JWTAuth::fromUser($customer);
            } else {
                $customer = $this->customer->registerFacebook($request->all());
                $token = JWTAuth::fromUser($customer);
            }
        }  else {
            $token = JWTAuth::fromUser($customer);
        }

        return response()->json([
            'msg' => 'successful', 'code' => 200, 'token' => $token, 'remember_token' => $customer->remember_token,
            'customer' => $customer->id, 'customer_img' => $customer->pro_pic
        ]);
    }

    public function register(Request $request)
    {
        if ($msg = $this->_validateRegistration($request)) {
            return api_response($request, null, 400, ['msg' => $msg, 'message' => $msg]);
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
        return api_response($request, null, 409, ['msg' => 'Already registered!', 'message' => 'Already registered!']);
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
