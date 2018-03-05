<?php

namespace App\Http\Controllers;


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
}