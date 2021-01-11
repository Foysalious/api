<?php namespace App\Http\Controllers\Customer;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\CustomerDeliveryAddress;
use App\Models\Partner;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Map\MapClientErrorException;
use Sheba\CustomerDeliveryAddress\AvailabilityChecker;
use Sheba\CustomerDeliveryAddress\Creator;
use Sheba\Location\Geo;
use Sheba\Map\Address;
use Sheba\Map\GeoCode;
use Sheba\ModificationFields;

class CustomerAddressController extends Controller
{
    use ModificationFields;

    public function isAvailable($customer, $address, Request $request, AvailabilityChecker $availability_checker)
    {
        $this->validate($request, ['partner' => 'required|numeric']);
        $delivery_address = CustomerDeliveryAddress::withTrashed()->where([['customer_id', $customer], ['id', $address]])->first();
        $available = $availability_checker->setAddress($delivery_address)->setPartner(Partner::find($request->partner))->isAvailable();
        return api_response($request, $available, 200, ['address' => [
            'is_available' => $available ? 1 : 0
        ]]);
    }

    public function store($customer, Request $request, GeoCode $geo_code, Address $address, Creator $creator, Geo $geo_class)
    {
        try {
            $this->validate($request, [
                'name' => 'required_if:is_save,1|string',
                'house_no' => 'required|string',
                'road_no' => 'required|string',
                'block_no' => 'string',
                'sector_no' => 'string',
                'city' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'is_save' => 'numeric|in:0,1',
            ]);
            $address_text = $request->house_no . ',' . $request->road_no;
            if ($request->has('block_no')) $address_text .= ',' . $request->block_no;
            if ($request->has('sector_no')) $address_text .= ',' . $request->sector_no;
            $address_text .= ',' . $request->city;
            $address->setAddress($address_text);
            $geo = $geo_code->setAddress($address)->getGeo();
            if (!$geo) $geo = $geo_class->setLat($request->lat)->setLng($request->lng);
            $this->setModifier($request->customer);
            $address = $creator->setCustomer($request->customer)->setAddressText($address_text)->setHouseNo($request->house_no)->setRoadNo($request->road_no)->setBlockNo($request->block_no)
                ->setSectorNo($request->sector_no)->setCity($request->city)->setGeo($geo)->setName($request->name)->setIsSave((int)$request->is_save)->create();
            return api_response($request, $address, 200, ['address' => [
                'id' => $address->id,
                'lat' => $geo->getLat(),
                'lng' => $geo->getLng(),
                'address' => $address->address,
                'flat_no' => $address->flat_no,
                'road_no' => $address->road_no,
                'house_no' => $address->house_no,
                'block_no' => $address->block_no,
                'sector_no' => $address->sector_no,
                'city' => $address->city,
                'street_address' => $address->street_address,
                'landmark' => $address->landmark,
            ]]);
        } catch (HyperLocationNotFoundException $e) {
            return response()->json(['message' => 'Your are out of service area.', 'code' => 402]);
        }
    }
}
