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
            $this->validate($request, [
                'house_no' => 'required|string',
                'road_no' => 'required|string',
                'block_no' => 'string',
                'sector_no' => 'string',
                'city' => 'required|string',
                'city_id' => 'required'
            ]);
            $address_text = $request->house_no . ',' . $request->road_no;
            if ($request->has('block_no')) $address_text .= ',' . $request->block_no;
            if ($request->has('sector_no')) $address_text .= ',' . $request->sector_no;
            $address_text .= ',' . $request->city;
            $geo = $geo_code->setAddress($address->setAddress($address_text))->getGeo();
        } catch (\Throwable $e) {
            dd($e);
        }
    }
}