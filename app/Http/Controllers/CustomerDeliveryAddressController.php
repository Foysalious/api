<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerDeliveryAddressController extends Controller
{

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $addresses = $customer->delivery_addresses()->select('id', 'address')->get();
            return api_response($request, null, 200, ['addresses' => $addresses, 'name' => $customer->profile->name, 'mobile' => $customer->profile->mobile]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $delivery_address = new CustomerDeliveryAddress();
            $delivery_address->address = $request->address;
            $delivery_address->name = $request->name;
            $delivery_address->customer_id = $customer->id;
            if ($delivery_address->save()) {
                return api_response($request, 1, 200, ['address' => $delivery_address->id]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function update($customer, $delivery_address, Request $request)
    {
        try {
            $this->validate($request, [
                'address' => 'required|string'
            ]);
            $delivery_address = CustomerDeliveryAddress::find((int)$delivery_address);
            if (!$delivery_address) {
                return api_response($request, null, 404);
            }
            if ($delivery_address->customer_id != $customer) {
                return api_response($request, null, 403);
            }
            $delivery_address->address = $request->address;
            $delivery_address->update();
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function destroy($customer, $delivery_address, Request $request)
    {
        try {
            $address = CustomerDeliveryAddress::where([['id', $delivery_address], ['customer_id', (int)$customer]])->first();
            if ($address) {
                $address->delete();
                return api_response($request, null, 200);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}