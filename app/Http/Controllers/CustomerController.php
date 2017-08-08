<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailVerficationEmail;
use App\Jobs\SendReferralRequestEmail;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\CustomerMobile;
use App\Models\Voucher;
use App\Repositories\CustomerRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Cache;
use App\Http\Controllers\FacebookAccountKit;
use Redis;
use Sheba\Voucher\ReferralCreator;
use Validator;

class CustomerController extends Controller
{
    use DispatchesJobs;
    private $customer;
    private $fbKit;

    public function __construct()
    {
        $this->customer = new CustomerRepository();
        $this->fbKit = new FacebookAccountKit();
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

    /**
     * @param $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerInfo($customer)
    {
        $cus = Customer::find($customer);
        $customer = Customer::select('name', 'xp', 'rating')
            ->find($customer);
//        'secondary_mobiles' => $cus->mobiles()->select('mobile')->where('mobile', '<>', $customer->mobile)->get(),
        return response()->json([
            'msg' => 'successful', 'code' => 200, 'customer' => $customer
        ]);
//        $cus = Customer::find($customer);
//        $customer = Customer::select('name', 'mobile', 'email', 'address', 'office_address', 'gender', 'dob', 'fb_id', 'pro_pic', 'xp', 'rating', 'reference_code', 'email_verified')
//            ->find($customer);
////        'secondary_mobiles' => $cus->mobiles()->select('mobile')->where('mobile', '<>', $customer->mobile)->get(),
//        return response()->json([
//            'msg' => 'successful', 'code' => 200, 'customer' => $customer,
//            'addresses' => $cus->delivery_addresses()->select('id', 'address')->get()
//        ]);
    }

    public function getCustomerGeneralInfo($customer)
    {
        $customer = Customer::find($customer);
        $adresses = $customer->delivery_addresses()->select('id', 'address')->get();
        $customer = $customer->profile()->select('name', 'address', 'gender', 'dob', 'email', 'mobile')->first();
        return response()->json([
            'msg' => 'successful', 'code' => 200, 'customer' => $customer, 'addresses' => $adresses
        ]);
    }

    public function getIntercomInfo($customer)
    {
        $customer = Customer::select('id', 'name', 'mobile', 'email', 'created_at')->where('id', $customer)->first();
        $user_hash = hash_hmac(
            'sha256', // hash function
            $customer->id, // user's email address
            env('INTERCOM_SECRET_KEY')// secret key (keep safe!)
        );
        array_add($customer, 'signed_up_at', $customer->created_at->timestamp);
        array_add($customer, 'user_hash', $user_hash);
        if (count($customer) != 0) {
            return response()->json(['msg' => 'successful', 'code' => 200, 'customer' => $customer]);
        } else {
            return response()->json(['msg' => 'not ok', 'code' => 404]);
        }
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

    public function getDeliveryInfo($customer)
    {
        $customer = Customer::find($customer);
        return response()->json(['msg' => 'successful', 'addresses' => $customer->delivery_addresses()->select('id', 'address')->get(),
            'name' => $customer->profile->name, 'mobile' => $customer->profile->mobile, 'code' => 200]);
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
        $profile = (Customer::find($customer))->profile;
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

    public function getReferral($customer)
    {
        $customer = Customer::find($customer);
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
        $customer = Customer::find($customer);
        $this->dispatch(new SendReferralRequestEmail($customer, $request->email, $customer->referral));
        return response()->json(['code' => 200]);
    }

    private function _validateEditInfo($request, $profile)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:profiles,email,' . $profile->id,
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required|date|date_format:Y-m-d|before:' . date('Y-m-d')
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
