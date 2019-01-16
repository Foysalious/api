<?php namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Partner;
use App\Models\Profile;
use App\Sheba\Geo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\ModificationFields;

class CustomerDeliveryAddressController extends Controller
{
    use ModificationFields;

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $location = null;
            $customer_delivery_addresses = $customer->delivery_addresses()->select('id', 'location_id', 'address', 'name', 'geo_informations')->get();
            if ($request->has('lat') && $request->has('lng')) {
                $hyper_location = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->first();
                if ($hyper_location) $location = $hyper_location->location;
                if ($location == null) return api_response($request, null, 404, ['message' => "No address at this location"]);
            }
            $customer_order_addresses = $customer->orders()->selectRaw('delivery_address,count(*) as c')->groupBy('delivery_address')->orderBy('c', 'desc')->get();
            $customer_delivery_addresses = $customer_delivery_addresses->map(function ($customer_delivery_address) use ($customer_order_addresses) {
                $customer_delivery_address['count'] = $this->getOrderCount($customer_order_addresses, $customer_delivery_address);
                $geo = json_decode($customer_delivery_address['geo_informations']);
                $customer_delivery_address['geo_informations'] = $geo ? array('lat' => (double)$geo->lat, 'lng' => (double)$geo->lng) : null;
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
            return api_response($request, $customer_delivery_addresses, 200, ['addresses' => $customer_delivery_addresses,
                'name' => $customer->profile->name, 'mobile' => $customer->profile->mobile]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function filterAddress($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'partner' => 'sometimes|numeric',
            ]);
            $customer = $request->customer;
            $location = null;
            $customer_delivery_addresses = $customer->delivery_addresses()->select('id', 'location_id', 'address', 'name', 'geo_informations')->get();
            $hyper_location = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->first();
            if ($hyper_location) $location = $hyper_location->location;
            if ($location == null) return api_response($request, null, 404, ['message' => "No address at this location"]);
            $customer_order_addresses = $customer->orders()->selectRaw('delivery_address,count(*) as c')->groupBy('delivery_address')->orderBy('c', 'desc')->get();
            $customer_delivery_addresses = $customer_delivery_addresses->reject(function ($address) {
                return $address->geo_informations == null;
            })->map(function ($customer_delivery_address) use ($customer_order_addresses) {
                $customer_delivery_address['count'] = $this->getOrderCount($customer_order_addresses, $customer_delivery_address);
                $geo = json_decode($customer_delivery_address['geo_informations']);
                $customer_delivery_address['geo_informations'] = $geo ? array('lat' => (double)$geo->lat, 'lng' => (double)$geo->lng) : null;
                $customer_delivery_address['is_valid'] = 1;
                return $customer_delivery_address;
            });
//            if ($location) $customer_delivery_addresses = $customer_delivery_addresses->where('location_id', $location->id);
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
            $customer_delivery_addresses = $customer_delivery_addresses->sortByDesc('count')->values()->all();
            return api_response($request, $customer_delivery_addresses, 200, ['addresses' => $customer_delivery_addresses,
                'name' => $customer->profile->name, 'mobile' => $customer->profile->mobile]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            $mobile = trim(str_replace(' ', '', $request->mobile));
            $request->merge(['address' => trim($request->address), 'mobile' => $mobile ?: $request->customer->profile->mobile]);
            $this->validate($request, ['address' => 'required|string']);
            $customer = $request->customer;
            $hyper_local = $request->has('lat') && $request->has('lng');
            if (!$hyper_local) {
                if ($geo = (new Geo())->geoCodeFromPlace($request->address)) {
                    $request->merge(['lat' => $geo['lat'], 'lng' => $geo['lng']]);
                    $request->merge(["geo_informations" => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng])]);
                }
            }
            if ($hyper_local) {
                $hyper_local = HyperLocal::insidePolygon($request->lat, $request->lng)->with('location')->first();
                if (!$hyper_local) return api_response($request, null, 400, ['message' => "You're out of our service area."]);
                $request->merge(["geo_informations" => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng])]);
            }
            $request->merge(["location_id" => $hyper_local ? $hyper_local->location_id : null]);
            $new_address = new CustomerDeliveryAddress();
            $delivery_address = $this->_store($customer, $new_address, $request);
            return api_response($request, 1, 200, ['address' => $delivery_address->id]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

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
        return $delivery_address;
    }

    public function update($customer, $delivery_address, Request $request)
    {
        try {
            $request->merge(['address' => trim($request->address)]);
            $this->validate($request, ['address' => 'required|string']);
            $customer = $request->customer;
            $delivery_address = CustomerDeliveryAddress::find((int)$delivery_address);
            if (!$delivery_address) return api_response($request, null, 404, ['message' => 'Address not found']);
            if ($delivery_address->customer_id != $customer->id) return api_response($request, null, 403, ['message' => "This is not your address."]);
            if (!$request->has('lat') && !$request->has('lng')) {
                $geo = (new Geo())->geoCodeFromPlace($request->address);
                if ($geo) $request->merge(['lat' => $geo['lat'], 'lng' => $geo['lng']]);
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
     * @throws \Exception
     */
    private function _delete(Customer $customer, CustomerDeliveryAddress $address)
    {
        $this->setModifier($customer);
        $this->withUpdateModificationField($address);
        $address->update();
        $address->delete();
    }

    public function destroy($customer, $delivery_address, Request $request)
    {
        try {
            $address = CustomerDeliveryAddress::where([['id', $delivery_address], ['customer_id', (int)$customer]])->first();
            if ($address) {
                $this->_delete($request->customer, $address);
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

    public function getDeliveryInfoForAffiliate(Request $request)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required|mobile:bd'
            ]);
            $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
            if ($profile && $profile->customer) {
                $customer = $profile->customer;
                $customer_delivery_addresses = $customer->delivery_addresses()->select('id', 'address', 'geo_informations')->get()->reject(function ($address) {
                    return $address->geo_informations == null;
                })->map(function ($customer_delivery_address) {
                    $customer_delivery_address["address"] = scramble_string($customer_delivery_address["address"]);
                    $customer_delivery_address['geo_informations'] = json_decode($customer_delivery_address['geo_informations']);
                    return $customer_delivery_address;
                })->filter(function ($customer_delivery_address) use ($request) {
                    if($request->has('lat') && $request->has('lng')) {
                        $geo_informations= $customer_delivery_address['geo_informations'];
                        if((float) $request->lat !== $geo_informations->lat ||  (float) $request->lng !== $geo_informations->lng)
                            return false;
                    }
                    return $customer_delivery_address->address != null;
                })->values()->all();
                return api_response($request, $customer_delivery_addresses, 200, ['addresses' => $customer_delivery_addresses]);
            }
            return api_response($request, [], 404, ['addresses' => []]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeDeliveryAddressForAffiliate(Request $request)
    {
        $profile = Profile::where('mobile', formatMobile($request->mobile))->first();
        if ($profile && $profile->customer) {
            $customer = $profile->customer;
            return $this->storeForAffiliate($customer,$request);
        }
        return api_response($request, [], 404, ['addresses' => []]);
    }

    public function storeForAffiliate($customer, Request $request)
    {
        try {
            $mobile = trim(str_replace(' ', '', $request->mobile));
            $request->merge(['address' => trim($request->address), 'mobile' => $mobile ?: $request->customer->profile->mobile]);
            $this->validate($request, ['address' => 'required|string']);

            $hyper_local = $request->has('lat') && $request->has('lng');
                if (!$hyper_local) {
                if ($geo = (new Geo())->geoCodeFromPlace($request->address)) {
                    $request->merge(['lat' => $geo['lat'], 'lng' => $geo['lng']]);
                    $request->merge(["geo_informations" => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng])]);
                }
            }
            if ($hyper_local) {
                $hyper_local = HyperLocal::insidePolygon($request->lat, $request->lng)->with('location')->first();
                if (!$hyper_local) return api_response($request, null, 400, ['message' => "You're out of our service area."]);
                $request->merge(["geo_informations" => json_encode(['lat' => (double)$request->lat, 'lng' => (double)$request->lng])]);
            }
            $request->merge(["location_id" => $hyper_local ? $hyper_local->location_id : null]);
            $new_address = new CustomerDeliveryAddress();
            $delivery_address = $this->_store($customer, $new_address, $request);
            return api_response($request, 1, 200, ['address' => $delivery_address->id]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}