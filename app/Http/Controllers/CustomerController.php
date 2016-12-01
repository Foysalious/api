<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\CustomerMobile;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Cache;

class CustomerController extends Controller {
    protected $customer;

    public function __construct()
    {
        $this->customer = new CustomerRepository();
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
        $customer = Customer::select('name', 'mobile', 'email', 'address', 'office_address', 'gender', 'dob', 'pro_pic', 'xp', 'rating', 'reference_code')
            ->find($customer);
        return response()->json(['msg' => 'successful', 'code' => 200, 'customer' => $customer]);
    }

    public function facebookIntegration(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $customer = $this->customer->updateCustomerInfo($customer, $request->all());
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

    public function addSecondaryMobile(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        if ($this->customer->checkSecondaryMobile($request->input('secondary_mobile')))
        {
            $customer_mobile = new CustomerMobile();
            $customer_mobile->mobile = $request->input('secondary_mobile');
            $customer_mobile->customer_id = $customer->id;
            if ($customer_mobile->save())
            {
                return response()->json(['msg' => 'successful', 'code' => 200]);
            }
        }
        return response()->json(['msg' => 'already exists', 'code' => 409]);

    }

    public function addDeliveryAddress(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $delivery_address = new CustomerDeliveryAddress();
        $delivery_address->address = $request->input('delivery_address');
        $delivery_address->customer_id = $customer->id;
        $delivery_address->save();
        return response()->json(['msg' => 'successful', 'code' => 200]);
    }
}
