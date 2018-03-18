<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailVerficationEmail;
use App\Jobs\SendReferralRequestEmail;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\CustomerMobile;
use App\Models\Job;
use App\Models\Order;
use App\Models\Profile;
use App\Repositories\CustomerRepository;
use App\Repositories\FileRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Cache;
use Illuminate\Validation\ValidationException;
use Redis;
use Sheba\Voucher\ReferralCreator;
use Validator;
use DB;
use Hash;

class CustomerController extends Controller
{
    use DispatchesJobs;
    private $customer;
    private $fbKit;
    private $fileRepository;

    public function __construct()
    {
        $this->customer = new CustomerRepository();
        $this->fbKit = new FacebookAccountKit();
        $this->fileRepository = new FileRepository();
    }

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer->load(['profile' => function ($q) {
                $q->select('id', 'name', 'password', 'address', DB::raw('pro_pic as picture'), 'gender', DB::raw('dob as birthday'), 'email', 'mobile');
            }]);
            $profile = $customer->profile;
            $profile->password = ($profile->password) ? 1 : 0;
            return api_response($request, $customer->profile, 200, ['profile' => $customer->profile]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function update($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'field' => 'required|string|in:name,birthday,gender,address',
                'value' => 'required|string'
            ]);
            $customer = $request->customer;
            $field = $request->field;
            $profile = $customer->profile;
            if ($field == 'birthday') {
                $this->validate($request, [
                    'value' => 'required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                ]);
                $profile->dob = $request->value;
            } elseif ($field == 'gender') {
                $this->validate($request, [
                    'value' => 'required|string|in:Male,Female,Other',
                ]);
                $profile->gender = $request->value;
            } else {
                $this->validate($request, [
                    'value' => 'required|string'
                ]);
                $profile->$field = trim($request->value);
            }
            $profile->update();
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function updateEmail($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email|unique:profiles'
            ]);
            $profile = $request->customer->profile;
            $profile->email = $request->email;
            $profile->update();
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function updatePassword($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'new_password' => 'required|string|min:6',
                'old_password' => 'sometimes|string',
            ]);
            $profile = $request->customer->profile;
            if ($profile->password) {
                if (!Hash::check($request->old_password, $profile->password)) {
                    return api_response($request, null, 403, ['message' => "Old password doesn't match"]);
                }
            }
            $profile->password = bcrypt($request->new_password);
            $profile->update();
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function updatePicture($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'picture' => 'required|mimes:jpeg,png'
            ]);
            $profile = $request->customer->profile;
            $photo = $request->file('picture');
            if (basename($profile->pro_pic) != 'default.jpg') {
                $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
                $this->fileRepository->deleteFileFromCDN($filename);
            }
            $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . '.' . $photo->extension();
            $picture_link = $this->fileRepository->uploadToCDN($filename, $photo, 'images/profiles/');
            if ($picture_link != false) {
                $profile->pro_pic = $picture_link;
                $profile->update();
                return api_response($request, $profile, 200, ['picture' => $profile->pro_pic]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function updateMobile($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'code' => 'required|string'
            ]);
            $code_data = $this->fbKit->authenticateKit($request->code);
            if ($code_data) {
                $mobile = formatMobile($code_data['mobile']);
                $mobile_profile = Profile::where('mobile', $mobile)->first();
                if ($mobile_profile == null) {
                    $profile = $request->customer->profile;
                    $profile->mobile = $mobile;
                    $profile->mobile_verified = 1;
                    $profile->update();
                } else {
                    return api_response($request, null, 403, ['message' => 'Mobile already exits!']);
                }
            }
            return api_response($request, null, 409);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function emailVerification(Customer $customer, $code)
    {
        $verification_code = Cache::get($customer->id . '-verification-email');
        if (empty($verification_code)) {
            Cache::forget($customer->id . '-verification-email');
            return "Code has expired";
        } elseif ($verification_code == $code) {
            $message = $this->customer->emailVerified($customer);
            Cache::forget($customer->id . '-verification-email');
            return $message;
        } else {
            return "Verification code doesn't match!";
        }
    }

    public function getCustomerInfo($customer, Request $request)
    {
        return response()->json([
            'msg' => 'successful', 'code' => 200, 'customer' => $request->customer->profile()->select('name', DB::raw('pro_pic as picture'))->first()
        ]);
    }

    public function getCustomerGeneralInfo($customer, Request $request)
    {
        $customer = $request->customer;
        $adresses = $customer->delivery_addresses()->select('id', 'address')->get();
        $customer = $customer->profile()->select('name', 'address', DB::raw('pro_pic as picture'), 'gender', DB::raw('dob as birthdate'), 'email', 'mobile')->first();
        return response()->json([
            'msg' => 'successful', 'code' => 200, 'customer' => $customer, 'addresses' => $adresses
        ]);
    }

    public function getIntercomInfo($customer, Request $request)
    {
        $customer = $request->customer->profile()->select('id', 'name', 'mobile', 'email')->first();
        $user_hash = hash_hmac('sha256', $customer->id, env('INTERCOM_SECRET_KEY'));
        array_add($customer, 'signed_up_at', $request->customer->created_at->timestamp);
        array_add($customer, 'user_hash', $user_hash);
        return response()->json(['msg' => 'successful', 'code' => 200, 'customer' => $customer]);
    }

    public function facebookIntegration(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $customer = $this->customer->updateCustomerInfo($customer, $request->all());
        return response()->json(['msg' => 'successful', 'code' => 200, 'fb' => $customer->fb_id, 'img' => $customer->pro_pic]);
    }

    public function changeAddress(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        if ($request->has('office_address'))
            $customer->office_address = $request->input('office_address');
        if ($request->has('address'))
            $customer->address = $request->input('address');
        if ($customer->update())
            return response()->json(['msg' => 'successful', 'code' => 200]);

    }

    public function modifyMobile(Request $request, $customer)
    {
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        $customer = Customer::find($customer);
        if ($this->customer->mobileValid($code_data['mobile'])) {
            $customer->mobile = $code_data['mobile'];
            $customer->mobile_verified = 1;
            if ($customer->update()) {
                $cus_mobile = new CustomerMobile();
                $cus_mobile->customer_id = $customer->id;
                $cus_mobile->mobile = $customer->mobile;
                $cus_mobile->save();
                return response()->json(['msg' => 'successful', 'code' => 200, 'mobile' => $customer->mobile]);
            }
        } else {
            return response()->json(['msg' => 'already exists', 'code' => 409]);
        }
    }

    public function addSecondaryMobile(Request $request, $customer)
    {
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        $customer = Customer::find($customer);
        if ($this->customer->mobileValid($code_data['mobile'])) {
            $customer_mobile = new CustomerMobile();
            $customer_mobile->mobile = $code_data['mobile'];
            $customer_mobile->customer_id = $customer->id;
            if ($customer_mobile->save()) {
                return response()->json(['msg' => 'successful', 'mobile' => $customer_mobile->mobile,
                    'mobile_id' => $customer_mobile->id, 'code' => 200]);
            }
        } else {
            return response()->json(['msg' => 'already exists', 'code' => 409]);
        }
    }

    public function addDeliveryAddress(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $delivery_address = new CustomerDeliveryAddress();
        $delivery_address->address = $request->input('delivery_address');
        $delivery_address->customer_id = $customer->id;
        if ($delivery_address->save()) {
            return response()->json(['msg' => 'successful', 'address_id' => $delivery_address->id, 'code' => 200]);
        } else {
            return response()->json(['msg' => 'error', 'code' => 500]);
        }
    }

    public function getDeliveryInfo($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            return api_response($request, null, 200, ['addresses' => $customer->delivery_addresses()->select('id', 'address')->get(),
                'name' => $customer->profile->name, 'mobile' => $customer->profile->mobile]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function removeDeliveryAddress($customer, Request $request)
    {
        $customer = Customer::find($customer);
        $delivery_address_id = $customer->delivery_addresses()->pluck('id');
        if ($delivery_address_id->contains($request->input('address_id'))) {
            $address = CustomerDeliveryAddress::find($request->input('address_id'));
            if ($address->delete())
                return response()->json(['msg' => 'successful', 'code' => 200]);
            else
                return response()->json(['msg' => 'error', 'code' => 500]);
        } else
            return response()->json(['msg' => 'unauthorized', 'code' => 409]);
    }

    public function modifyEmail(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        if ($email = $this->customer->ifExist($request->input('email'), 'email')) {
            return response()->json(['msg' => 'email already exists', 'code' => 409]);
        } else {
            $customer->email = $request->input('email');
            $customer->email_verified = 0;
            $customer->update();
            $this->dispatch(new SendEmailVerficationEmail($customer));
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
    }

    public function checkEmailVerification(Request $request, $customer_id)
    {
        $key = Redis::get('email-verification-' . $customer_id);
        if ($key != null && $key == $request->input('e_token')) {
            Redis::del('email-verification-' . $customer_id);
            $customer = Customer::find($customer_id);
            $customer->email_verified = 1;
            if ($customer->update()) {
                return response()->json(['msg' => 'successful', 'code' => 200]);
            }
        } else {
            return response()->json(['msg' => 'not found', 'code' => 404]);
        }
    }

    public function sendVerificationLink($customer)
    {
        $customer = Customer::find($customer);
        $this->dispatch(new SendEmailVerficationEmail($customer));
        return response()->json(['msg' => 'successful', 'code' => 200]);
    }

    public function removeSecondaryMobile($customer, Request $request)
    {
        $customer_mobile = CustomerMobile::where([
            ['mobile', $request->input('mobile')],
            ['customer_id', $customer]
        ])->first();
        if ($customer_mobile->delete()) {
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
    }

    public function setPrimaryMobile($customer, Request $request)
    {
        $customer = Customer::find($customer);
        $customer_mobile = CustomerMobile::where('mobile', $request->input('mobile'))->first();
        $customer_mobile->mobile = $customer->mobile;
        $customer_mobile->update();
        $customer->mobile = $request->input('mobile');
        if ($customer->update()) {
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
    }

    public function editInfo($customer, Request $request)
    {
        $profile = $request->customer->profile;
        if ($msg = $this->_validateEditInfo($request, $profile)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $profile->name = $request->name;
        $profile->email = $request->email;
        $profile->gender = $request->gender;
        $profile->dob = $request->dob;
        if ($profile->update()) {
            return response()->json(['msg' => 'successful', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'something went wrong!', 'code' => 500]);
        }
    }

    public function getReferral($customer, Request $request)
    {
        $customer = $request->customer;
        if ($customer->referral == '') {
            $referral_creator = new ReferralCreator($customer);
            $voucher = $referral_creator->create();
            return response()->json(['referral_code' => $voucher->code, 'name' => $customer->name, 'code' => 200]);
        }
        return response()->json(['referral_code' => $customer->referral->code, 'name' => $customer->name, 'code' => 200]);
    }

    public function sendReferralRequestEmail($customer, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 500, 'msg' => $validator->errors()->all()[0]]);
        }
        $customer = $request->customer;
        $this->dispatch(new SendReferralRequestEmail($customer, $request->email, $customer->referral));
        return response()->json(['code' => 200]);
    }

    private function _validateEditInfo($request, $profile)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:profiles,email,' . $profile->id,
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'sometimes|required|date|date_format:Y-m-d|before:' . date('Y-m-d')
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    public function getNotifications($customer, Request $request)
    {
        $customer = $request->customer;
        $notifications = ($customer->notifications()->select('id', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->get())->sortByDesc('created_at');
        $notifications->map(function ($notification) {
            $notification->event_type = str_replace('App\Models\\', "", $notification->event_type);
            array_add($notification, 'time', $notification->created_at->format('j M \\a\\t h:i A'));
            array_forget($notification, 'created_at');
            if ($notification->event_type == 'Job') {
                array_add($notification, 'event_code', (Job::find($notification->event_id))->fullCode());
            } elseif ($notification->event_type == 'Order') {
                array_add($notification, 'event_code', (Order::find($notification->event_id))->code());
            }
            return $notification;
        });
        if (count($notifications) != 0) {
            return api_response($request, $notifications, 200, ['notifications' => $notifications->values()->all()]);
        } else {
            return api_response($request, null, 404);
        }
    }
}
