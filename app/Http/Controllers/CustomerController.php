<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\CustomerMobile;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Cache;
use App\Http\Controllers\FacebookAccountKit;

class CustomerController extends Controller {
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

    /**
     * @param $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerInfo($customer)
    {
        $cus = Customer::find($customer);
        $customer = Customer::select('name', 'mobile', 'email', 'address', 'office_address', 'gender', 'dob', 'pro_pic', 'xp', 'rating', 'reference_code', 'email_verified')
            ->find($customer);
        return response()->json([
            'msg' => 'successful', 'code' => 200, 'customer' => $customer,
            'mobiles' => $cus->mobiles()->select('mobile')->get(),
            'addresses' => $cus->delivery_addresses()->select('id', 'address')->get()
        ]);

    }

    public function facebookIntegration(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $this->customer->updateCustomerInfo($customer, $request->all());
        return response()->json(['msg' => 'successful', 'code' => 200]);
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
        if ($this->customer->mobileValid($code_data['mobile']))
        {
            $customer->mobile = $code_data['mobile'];
            $customer->mobile_verified = 1;
            if ($customer->update())
            {
                return response()->json(['msg' => 'successful', 'code' => 200]);
            }
        }
        else
        {
            return response()->json(['msg' => 'already exists', 'code' => 409]);
        }
    }

    public function addSecondaryMobile(Request $request, $customer)
    {
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        $customer = Customer::find($customer);
        if ($this->customer->mobileValid($code_data['mobile']))
        {
            $customer_mobile = new CustomerMobile();
            $customer_mobile->mobile = $code_data['mobile'];
            $customer_mobile->customer_id = $customer->id;
            if ($customer_mobile->save())
            {
                return response()->json(['msg' => 'successful', 'code' => 200]);
            }
        }
        else
        {
            return response()->json(['msg' => 'already exists', 'code' => 409]);
        }
    }

    public function addDeliveryAddress(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $delivery_address = new CustomerDeliveryAddress();
        $delivery_address->address = $request->input('delivery_address');
        $delivery_address->customer_id = $customer->id;
        if ($delivery_address->save())
        {
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
        else
        {
            return response()->json(['msg' => 'error', 'code' => 500]);
        }
    }

    public function getDeliveryInfo($customer)
    {
        $customer = Customer::find($customer);
        return response()->json(['msg' => 'successful', 'addresses' => $customer->delivery_addresses, 'name' => $customer->name, 'mobile' => $customer->mobile, 'code' => 200]);
    }

    public function removeDeliveryAddress($customer, Request $request)
    {
        $customer = Customer::find($customer);
        $delivery_address_id = $customer->delivery_addresses()->pluck('id');
        if ($delivery_address_id->contains($request->input('address_id')))
        {
            $address = CustomerDeliveryAddress::find($request->input('address_id'));
            if ($address->delete())
                return response()->json(['msg' => 'successful', 'code' => 200]);
            else
                return response()->json(['msg' => 'error', 'code' => 500]);
        }
        else
            return response()->json(['msg' => 'unauthorized', 'code' => 409]);
    }

    public function modifyEmail(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        if ($email = $this->customer->ifExist($request->input('email'), 'email'))
        {
            return response()->json(['msg' => 'email already exists', 'code' => 409]);
        }
        else
        {
            $customer->email = $request->input('email');
            $customer->email_verified = 0;
            $customer->update();
            $this->customer->sendVerificationMail($customer);
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
    }

    public function sendVerificationLink($customer)
    {
        $customer = Customer::find($customer);
        $this->customer->sendVerificationMail($customer);
        return response()->json(['msg' => 'successful', 'code' => 200]);
    }

    public function removeSecondaryMobile($customer, Request $request)
    {
        $customer_mobile = CustomerMobile::where([
            ['mobile', $request->input('mobile')],
            ['customer_id', $customer]
        ])->first();
        if ($customer_mobile->delete())
        {
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
        if ($customer->update())
        {
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
    }

    public function modifyGeneralInfo($customer, Request $request)
    {
        $customer = Customer::find($customer);
        if ($request->has('name'))
        {
            $customer->name = $request->input('name');
        }
        if ($request->has('dob'))
        {
            $customer->dob = $request->input('dob');
        }
        if ($request->has('gender'))
        {
            $customer->gender = $request->input('gender');
        }
        if ($customer->update())
        {
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
    }
}
