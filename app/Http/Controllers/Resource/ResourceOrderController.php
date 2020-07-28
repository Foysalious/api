<?php namespace App\Http\Controllers\Resource;

use App\Models\Job;
use App\Models\Location;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Customer\Creator as CustomerCreator;
use Sheba\CustomerDeliveryAddress\Creator as CustomerDeliveryAddressCreator;
use Sheba\Jobs\AcceptJobAndAssignResource;
use Sheba\Location\Geo;
use Sheba\Order\Creator as OrderCreator;
use Sheba\Order\CheckAvailabilityForOrderPlace;
use Sheba\ServiceRequest\ServiceRequest;

class ResourceOrderController extends Controller
{
    public function store(Request $request, CustomerCreator $customerCreator, Geo $geo, CustomerDeliveryAddressCreator $deliveryAddressCreator, OrderCreator $orderCreator, CheckAvailabilityForOrderPlace $checkAvailabilityForOrderPlace, ServiceRequest $serviceRequest, AcceptJobAndAssignResource $acceptJobAndAssignResource)
    {
        $request->merge(['mobile' => formatMobile($request->mobile)]);
        $this->validate($request, [
            'mobile' => 'required|string|mobile:bd',
            'name' => 'required|string',
            'services' => 'required|string',
            'sales_channel' => 'required|string',
            'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'time' => 'required|string',
            'payment_method' => 'required|string|in:cod,online,wallet,bkash,cbl,partner_wallet',
            'location_id' => 'required|numeric',
            'address' => 'required|string',
            'partner' => 'required|numeric',
        ], ['mobile' => 'Invalid mobile number!']);

        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $location = Location::find($request->location_id);
        $geo_info = json_decode($location->geo_informations);
        $geo->setLat($geo_info->lat)->setLng($geo_info->lng);
        $serviceRequestObject = $serviceRequest->setServices(json_decode($request->services, 1))->get();
        $is_partner_available = $checkAvailabilityForOrderPlace->setGeo($geo)->setServiceRequestObject($serviceRequestObject)->setDate($request->date)->setTime($request->time)->setPartnerId($request->partner)->checkPartner();
        if (!$is_partner_available) return api_response($request, null, 403, ['message' => "Partner Not available"]);
        if ($request->assign_resource) {
            $is_resource_available = $checkAvailabilityForOrderPlace->setResource($resource)->checkResource();
            if (!$is_resource_available) return api_response($request, null, 403, ['message' => "Resource Not available"]);
        }
        $customer = $customerCreator->setMobile($request->mobile)->setName($request->name)->create();
        $address = $deliveryAddressCreator->setCustomer($customer)->setAddressText($request->address)->setGeo($geo)->setName($customer->profile->name)->create();
        $response = $orderCreator->setServices($request->services)->setCustomer($customer)->setMobile($request->mobile)
            ->setDate($request->date)->setTime($request->time)->setAddressId($address->id)->setAdditionalInformation($request->additional_information)
            ->setPartnerId($request->partner)->setSalesChannel($request->sales_channel)->setPaymentMethod($request->payment_method)->create();
        if ($request->assign_resource && $response->code == 200) {
            $job = Job::find($response->job_id);
            $partner = Partner::find($request->partner);
            $acceptJobAndAssignResource->setJob($job)->setPartner($partner)->setResource($resource)->setRequest($request)->acceptJobAndAssignResource();
        }
        return api_response($request, null, $response->code, ['message' => $response->message]);
    }
}
