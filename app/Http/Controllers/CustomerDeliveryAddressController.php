<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Sheba\Address\AddressValidator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class CustomerDeliveryAddressController extends Controller
{

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $location = null;
            if ($request->has('lat') && $request->has('lng')) {
                $hyper_location = HyperLocal::insidePolygon($request->lat, $request->lng)->first();
                $location = $hyper_location->location;
                if ($location == null) return api_response($request, null, 404, ['message' => "No address at this location"]);
            }
            $addresses = $customer->delivery_addresses()->select('id', 'address', 'name', 'mobile', 'location_id')->get();
            if ($location) $addresses = $addresses->where('location_id', $location->id);
            return api_response($request, null, 200, ['addresses' => $addresses->values()->all(), 'name' => $customer->profile->name, 'mobile' => $customer->profile->mobile]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            $request->merge(['address' => trim($request->address), 'mobile' => trim(str_replace(' ', '', $request->mobile))]);
            $customer = $request->customer;
            $addresses = $customer->delivery_addresses;
            $address_validator = new AddressValidator();
            if ($address_validator->isAddressNameExists($addresses, $request->address)) return api_response($request, null, 400, ['message' => "There is almost a same address exits with this name!"]);
            if ($address_validator->isAddressLocationExists($addresses, new Coords($request->lat, $request->lng))) return api_response($request, null, 400, ['message' => "There is already a address exits at this location!"]);
            $hyper_local = HyperLocal::insidePolygon($request->lat, $request->lng)->with('location')->first();
            $delivery_address = new CustomerDeliveryAddress();
            $delivery_address->customer_id = $customer->id;
            $delivery_address->geo_informations = json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng]);
            $delivery_address->location_id = $hyper_local->location_id;
            $delivery_address = $this->setAddressProperties($delivery_address, $request);
            createAuthor($delivery_address, $customer);
            $delivery_address->save();
            return api_response($request, 1, 200, ['address' => $delivery_address->id]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function setAddressProperties($delivery_address, $request)
    {
        $delivery_address->address = trim($request->address);
        if ($request->has('name')) $delivery_address->name = trim($request->name);
        if ($request->has('mobile')) $delivery_address->mobile = formatMobile($request->mobile);
        if ($request->has('flat_no')) $delivery_address->flat_no = trim($request->flat_no);
        if ($request->has('street_address')) $delivery_address->street_address = trim($request->street_address);
        if ($request->has('landmark')) $delivery_address->landmark = trim($request->landmark);
        return $delivery_address;
    }

    public function update($customer, $delivery_address, Request $request)
    {
        try {
            $this->validate($request, [
                'address' => 'required|string'
            ]);
            $customer = $request->customer;
            $delivery_address = CustomerDeliveryAddress::find((int)$delivery_address);
            if (!$delivery_address) {
                return api_response($request, null, 404, ['message' => 'Address not found']);
            }
            if ($delivery_address->customer_id != $customer->id) {
                return api_response($request, null, 403);
            }
            $delivery_address = $this->setAddressProperties($delivery_address, $request);
            updateAuthor($delivery_address, $customer);
            $delivery_address->update();
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
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
}