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
            $customer_order_addresses = $customer->orders()->selectRaw('delivery_address,count(*) as c')->groupBy('delivery_address')->orderBy('c', 'desc')->get();
            $customer_delivery_addresses = $customer->delivery_addresses()->select('id', 'address')->get()->map(function ($customer_delivery_address) use ($customer_order_addresses) {
                $customer_delivery_address['count'] = $this->getOrderCount($customer_order_addresses, $customer_delivery_address);
                return $customer_delivery_address;
            })->sortByDesc('count')->values()->all();
            return api_response($request, $customer_delivery_addresses, 200, ['addresses' => $customer_delivery_addresses,
                'name' => $customer->profile->name, 'mobile' => $customer->profile->mobile]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            if ($customer->delivery_addresses->where('address', $request->address)->first() !== null) {
                return api_response($request, null, 400, ['message' => "There is already a similar address exits!"]);
            }
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
            app('sentry')->captureException($e);
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
            if ($delivery_address->customer->delivery_addresses->where('address', $request->address)->first() !== null) {
                return api_response($request, null, 400, ['message' => "There is already a similar address exits!"]);
            }
            $delivery_address->address = $request->address;
            $delivery_address->update();
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getOrderCount($customer_order_addresses, $customer_delivery_address)
    {
        $count = 0;
        $customer_order_addresses->each(function ($customer_order_addresses) use ($customer_delivery_address, &$count) {
            similar_text($customer_delivery_address->address, $customer_order_addresses->delivery_address, $percent);
            if ($percent >= 80) $count = $customer_order_addresses->c;
        });
        return $count;
    }
}