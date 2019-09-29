<?php

namespace App\Http\Controllers;


use App\Models\CustomerDeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerAddressController extends Controller
{
    public function update($customer, $address, Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'sometimes|required|string',
                'address' => 'sometimes|required|string',
            ]);
            $delivery_address = CustomerDeliveryAddress::find($address);
            if ($request->customer->id != $delivery_address->customer_id) {
                return api_response($request, null, 403);
            }
            $delivery_address->update(array_filter($request->only(['name', 'address'])));
            return api_response($request, $delivery_address, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}