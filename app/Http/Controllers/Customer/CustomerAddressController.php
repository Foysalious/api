<?php namespace App\Http\Controllers\Customer;


use App\Http\Controllers\Controller;
use App\Models\CustomerDeliveryAddress;
use App\Models\Partner;
use Illuminate\Http\Request;
use Sheba\CustomerDeliveryAddress\AvailabilityChecker;
use Sheba\Map\Address;
use Sheba\Map\GeoCode;

class CustomerAddressController extends Controller
{
    public function isAvailable($customer, $address, Request $request, AvailabilityChecker $availability_checker)
    {
        $this->validate($request, ['partner' => 'required|numeric']);
        $delivery_address = CustomerDeliveryAddress::withTrashed()->where([['customer_id', $customer], ['id', $address]])->first();
        $available = $availability_checker->setAddress($delivery_address)->setPartner(Partner::find($request->partner))->isAvailable();
        return api_response($request, $available, 200, ['address' => [
            'is_available' => $available ? 1 : 0
        ]]);
    }

    public function store(Request $request, GeoCode $geo_code, Address $address)
    {
        try {
            $geo = $geo_code->setAddress($address->setAddress($request->address))->getGeo();
            dd($geo);
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}