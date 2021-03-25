<?php namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Location;
use Sheba\CustomerDeliveryAddress\CustomerDeliveryAddressManager;
use Sheba\Dal\LocationService\LocationService;
use App\Models\Partner;
use App\Models\Profile;
use App\Sheba\Address\AddressValidator;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Dal\CategoryLocation\CategoryLocation;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\Location\Geo;
use Sheba\Location\GeoBuilder;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\Customer\CustomerDeliveryAddressInterface;
use Throwable;

class CustomerDeliveryAddressController extends Controller
{
    use ModificationFields;

    /** @var CustomerDeliveryAddressManager */
    private $addressManager;
    /**
     * @var GeoBuilder
     */
    private $geoBuilder;

    /**
     * CustomerDeliveryAddressController constructor.
     * @param CustomerDeliveryAddressManager $address_manager
     * @param GeoBuilder $geo_builder
     */
    public function __construct(CustomerDeliveryAddressManager $address_manager, GeoBuilder $geo_builder)
    {
        $this->addressManager = $address_manager;
        $this->geoBuilder = $geo_builder;
    }

    /**
     * @param $customer
     * @param Request $request
     * @return JsonResponse
     */
    public function index($customer, Request $request)
    {
        $customer = $request->customer->load(['profile' => function ($q) {
            $q->select('id', 'name', 'mobile');
        }]);
        $location = null;
        $customer_delivery_addresses = $customer->delivery_addresses()->select('id', 'location_id', 'address', 'name', 'geo_informations', 'flat_no')->get();
        if ($request->has('lat') && $request->has('lng')) {
            $hyper_location = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->first();
            if ($hyper_location) $location = $hyper_location->location;
            if ($location == null) return api_response($request, null, 404, ['message' => "No address at this location"]);
        }
        $customer_order_addresses = $customer->orders()->selectRaw('delivery_address,count(*) as c')->groupBy('delivery_address')->orderBy('c', 'desc')->get();
        $customer_delivery_addresses = $customer_delivery_addresses->map(function ($customer_delivery_address) use ($customer_order_addresses) {
            if (empty($customer_delivery_address->name)) $customer_delivery_address['name'] = '';
            if (empty($customer_delivery_address->address)) $customer_delivery_address['address'] = '';
            $customer_delivery_address['count'] = $this->addressManager->getOrderCount($customer_order_addresses, $customer_delivery_address);
            $geo = json_decode($customer_delivery_address['geo_informations']);
            $customer_delivery_address['geo_informations'] = $geo ? ['lat' => (double)$geo->lat, 'lng' => (double)$geo->lng] : null;
            return $customer_delivery_address;
        });
        if ($location) $customer_delivery_addresses = $customer_delivery_addresses->where('location_id', $location->id);
        if ($request->has('partner') && (int)$request->partner) {
            $partner = Partner::find((int)$request->partner);
            $partner_geo = json_decode($partner->geo_informations);
            $to = [new Coords(floatval($partner_geo->lat), floatval($partner_geo->lng), $partner->id)];
            $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
            $customer_delivery_addresses = $customer_delivery_addresses->reject(function ($customer_delivery_address) {
                return $customer_delivery_address->geo_informations == null;
            })->filter(function ($customer_delivery_address) use ($distance, $to, $partner_geo) {
                $address_geo = $customer_delivery_address->geo_informations;
                $current = new Coords($address_geo['lat'], $address_geo['lng']);
                return $distance->from([$current])->to($to)->sortedDistance()[0][$to[0]->id] <= (double)$partner_geo->radius * 1000;
            });
        }

        $customer_delivery_addresses = $customer_delivery_addresses->sortByDesc('count')->values()->all();
        $customer_delivery_addresses = $this->addressManager->sortAddressesByHomeAndWork($customer_delivery_addresses);

        return api_response($request, $customer_delivery_addresses, 200, [
            'addresses' => $customer_delivery_addresses,
            'name' => $customer->profile->name,
            'mobile' => $customer->profile->mobile
        ]);
    }

    /**
     * @param $customer
     * @param Request $request
     * @param AddressValidator $address_validator
     * @param CustomerDeliveryAddressInterface $customer_delivery_address_repo
     * @return JsonResponse
     */
    public function filterAddress($customer, Request $request, AddressValidator $address_validator, CustomerDeliveryAddressInterface $customer_delivery_address_repo)
    {
        $this->validate($request, [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'partner' => 'sometimes|numeric',
            'service' => 'sometimes|string',
            'category' => 'sometimes|string',
        ]);
        $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        if (!is_null($hyperLocation)) $location = $hyperLocation->location->id;
        $customer = $request->customer;
        $customer_delivery_addresses = $customer_delivery_address_repo->getAddressesForOrderPlacement($customer->id)->where('location_id',$location)->get();
        $customer_order_addresses = $customer->orders()->selectRaw('delivery_address,count(*) as c')->groupBy('delivery_address')->orderBy('c', 'desc')->get();
        $target = new Coords((double)$request->lat, (double)$request->lng);
        $customer_delivery_addresses = $customer_delivery_addresses->reject(function ($address) {
            return $address->geo_informations == null;
        })->map(function (CustomerDeliveryAddress $customer_delivery_address) use ($customer_order_addresses, $target, $address_validator) {
            $customer_delivery_address['count'] = $this->addressManager->getOrderCount($customer_order_addresses, $customer_delivery_address);
            $geo = $customer_delivery_address->getGeo();
            $customer_delivery_address['geo_informations'] = $geo->isNull() ? null : $geo->toArray();
            $customer_delivery_address['is_valid'] = 1;
            $customer_delivery_address['is_same'] = $geo->isNull() ? false : $address_validator->isSameAddress($geo, $target);
            return $customer_delivery_address;
        });
        if ($request->has('partner') && (int)$request->partner > 0) {
            $partner = Partner::find((int)$request->partner);
            $partner_geo = json_decode($partner->geo_informations);
            $to = [new Coords(floatval($partner_geo->lat), floatval($partner_geo->lng), $partner->id)];
            $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
            $customer_delivery_addresses = $customer_delivery_addresses->reject(function ($customer_delivery_address) {
                return $customer_delivery_address->geo_informations == null;
            })->map(function ($customer_delivery_address) use ($distance, $to, $partner_geo) {
                $address_geo = $customer_delivery_address->geo_informations;
                $current = new Coords($address_geo['lat'], $address_geo['lng']);
                $inside_radius = ($distance->from([$current])->to($to)->sortedDistance()[0][$to[0]->id] <= (double)$partner_geo->radius * 1000) ? 1 : 0;
                $customer_delivery_address['is_valid'] = $inside_radius;
                return $customer_delivery_address;
            });
        }
        if ($request->has('service')) {
            $service = array_map('intval', json_decode($request->service));
            $location_service = LocationService::whereIn('service_id', $service)->select('location_id')->get();
            $location_ids = count($location_service) > 0 ? $location_service->pluck('location_id')->toArray() : [];
            $customer_delivery_addresses->map(function ($address) use ($location_ids) {
                if (!$address['is_valid']) return $address;
                $address['is_valid'] = in_array($address->location_id, $location_ids) ? 1 : 0;
                return $address;
            });
        }
        if ($request->has('category')) {
            $category = json_decode($request->category);
            if ($category) {
                $category = array_map('intval', json_decode($request->category));
                $category_location = CategoryLocation::whereIn('category_id', $category)->select('location_id')->get();
                $location_ids = count($category_location) > 0 ? $category_location->pluck('location_id')->toArray() : [];
                $customer_delivery_addresses->map(function ($address) use ($location_ids) {
                    if (!$address['is_valid']) return $address;
                    $address['is_valid'] = in_array($address->location_id, $location_ids) ? 1 : 0;
                    return $address;
                });
            }
        }

        $customer_delivery_addresses = $customer_delivery_addresses->sortByDesc('count')->sortByDesc('is_same')->values()->all();

        return api_response($request, $customer_delivery_addresses, 200, [
            'addresses' => $customer_delivery_addresses,
            'name' => $customer->profile->name,
            'mobile' => $customer->profile->mobile
        ]);
    }

    /**
     * @param $customer
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function store($customer, Request $request)
    {
        $mobile = trim(str_replace(' ', '', $request->mobile));
        $request->merge(['address' => trim($request->address), 'mobile' => $mobile ?: $request->customer->profile->mobile]);
        $this->validate($request, ['address' => 'required|string']);
        $customer = $request->customer;
        $delivery_address = $this->_create($request, $customer);
        return api_response($request, 1, 200, ['address' => $delivery_address->id]);
    }

    /**
     * @param CustomerDeliveryAddress $delivery_address
     * @param $request
     * @return CustomerDeliveryAddress
     */
    private function setAddressProperties(CustomerDeliveryAddress $delivery_address, $request)
    {
        if ($request->has('address')) $delivery_address->address = trim($request->address);
        if ($request->has('name')) $delivery_address->name = trim(ucwords($request->name));
        if ($request->has('location_id')) $delivery_address->location_id = $request->location_id;
        if ($request->has('mobile')) $delivery_address->mobile = formatMobile($request->mobile);
        if ($request->has('flat_no')) $delivery_address->flat_no = trim($request->flat_no);
        if ($request->has('street_address')) $delivery_address->street_address = trim($request->street_address);
        if ($request->has('landmark')) $delivery_address->landmark = trim($request->landmark);
        if ($request->has('lat') && $request->has('lng')) $delivery_address->geo_informations = json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng]);
        if ($request->has('is_save') && !$request->is_save) $delivery_address->deleted_at = Carbon::now();
        return $delivery_address;
    }

    /**
     * @param $customer
     * @param $delivery_address
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update($customer, $delivery_address, Request $request)
    {
        $request->merge(['address' => trim($request->address)]);
        $this->validate($request, ['address' => 'required|string']);
        $customer = $request->customer;
        $delivery_address = CustomerDeliveryAddress::find((int)$delivery_address);
        if (!$delivery_address) return api_response($request, null, 404, ['message' => 'Address not found']);
        if ($delivery_address->customer_id != $customer->id) return api_response($request, null, 403, ['message' => "This is not your address."]);
        if (!$request->has('lat') && !$request->has('lng')) {
            $geo = $this->geoBuilder->fromAddressString($request->address);
            if ($geo->isNotNull()) $request->merge($geo->toArray());
        }
        if ($request->has('lat') && $request->has('lng')) {
            $hyper_local = HyperLocal::insidePolygon($request->lat, $request->lng)->with('location')->first();
            if (!$hyper_local) return api_response($request, null, 400, ['message' => "You're out of our service area."]);
            $request->merge(["geo_informations" => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng])]);
        }
        $new_address = $delivery_address->replicate();
        $this->_store($customer, $new_address, $request);
        $this->_delete($customer, $delivery_address);
        return api_response($request, 1, 200);
    }

    /**
     * @param Customer $customer
     * @param CustomerDeliveryAddress $delivery_address
     * @param $request
     * @return CustomerDeliveryAddress
     */
    private function _store(Customer $customer, CustomerDeliveryAddress $delivery_address, $request)
    {
        $delivery_address = $this->setAddressProperties($delivery_address, $request);
        $delivery_address->customer_id = $customer->id;
        $this->setModifier($customer);
        $this->withCreateModificationField($delivery_address);
        $delivery_address->save();
        return $delivery_address;
    }

    /**
     * @param Customer $customer
     * @param CustomerDeliveryAddress $address
     * @throws Exception
     */
    private function _delete(Customer $customer, CustomerDeliveryAddress $address)
    {
        $this->setModifier($customer);
        $this->withUpdateModificationField($address);
        $address->update();
        $address->delete();
    }

    /**
     * @param $customer
     * @param $delivery_address
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy($customer, $delivery_address, Request $request)
    {
        $address = CustomerDeliveryAddress::where([['id', $delivery_address], ['customer_id', (int)$customer]])->first();
        if (!$address) return api_response($request, null, 404);
        $this->_delete($request->customer, $address);
        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDeliveryInfoForAffiliate(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile:bd'
        ]);
        $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
        if (!($profile && $profile->customer)) return api_response($request, [], 404, ['addresses' => []]);

        $request_geo = new Geo();
        if ($request->has('lat') && $request->has('lng')) {
            $request_geo->setLng($request->lng)->setLat($request->lat);
        }

        $customer = $profile->customer;
        $customer_delivery_addresses = $customer->delivery_addresses()->select('id', 'address', 'geo_informations', 'flat_no')->get()->reject(function ($address) {
            return $address->geo_informations == null;
        })->map(function ($customer_delivery_address) {
            $customer_delivery_address["address"] = scramble_string($customer_delivery_address["address"]);
            $customer_delivery_address['geo_informations'] = json_decode($customer_delivery_address['geo_informations']);
            return $customer_delivery_address;
        })->filter(function (CustomerDeliveryAddress $customer_delivery_address) use ($request_geo) {
            if ($request_geo->isNotNull() && $customer_delivery_address->getGeo()->isDifferentWith($request_geo))
                return false;
            return $customer_delivery_address->address != null;
        })->values()->all();
        return api_response($request, $customer_delivery_addresses, 200, ['addresses' => $customer_delivery_addresses]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeDeliveryAddressForAffiliate(Request $request)
    {
        $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
        if (!($profile && $profile->customer)) return api_response($request, [], 404, ['addresses' => []]);

        $customer = $profile->customer;
        return $this->store($customer, $request);
    }

    /**
     * @param Request $request
     * @param $customer
     * @return CustomerDeliveryAddress|JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function _create(Request $request, $customer)
    {
        $hyper_local = $request->has('lat') && $request->has('lng');

        if (!$hyper_local) {
            $geo = $this->geoBuilder->fromAddressString($request->address);
            if ($geo->isNotNull()) {
                $request->merge($geo->toArray());
                $request->merge(["geo_informations" => $geo->toJson()]);
            }
        }
        if ($hyper_local) {
            $hyper_local = HyperLocal::insidePolygon($request->lat, $request->lng)->with('location')->first();
            if (!$hyper_local) return api_response($request, null, 400, ['message' => "You're out of our service area."]);
            $request->merge(["geo_informations" => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng])]);
        }
        $request->merge(["location_id" => $hyper_local ? $hyper_local->location_id : null]);
        $new_address = new CustomerDeliveryAddress();
        return $this->_store($customer, $new_address, $request);
    }
}
