<?php namespace Sheba\OrderPlace;

use App\Models\Affiliation;
use App\Models\CarRentalJobDetail;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\InfoCall;
use App\Models\Job;
use App\Models\Location;
use App\Models\LocationService;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Sheba\Checkout\Services\RentACarServiceObject;
use Sheba\Checkout\Services\ServiceObject;
use Sheba\Dal\JobService\JobService;
use Sheba\Jobs\JobStatuses;
use Sheba\Jobs\PreferredTime;
use Sheba\Location\Geo;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\ModificationFields;
use Sheba\PartnerList\Director;
use Sheba\PartnerList\PartnerListBuilder;
use Sheba\PartnerOrderRequest\Creator;
use Sheba\RequestIdentification;
use DB;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\ServiceRequest\ServiceRequestObject;

class OrderPlace
{
    use ModificationFields;
    private $deliveryAddressId;
    /** @var CustomerDeliveryAddress */
    private $deliveryAddress;
    /** @var Collection */
    private $services;
    /** @var Category */
    private $category;
    private $salesChannel;
    private $deliveryMobile;
    private $deliveryName;
    private $paymentMethod;
    private $scheduleDate;
    private $scheduleTime;
    /** @var Customer */
    private $customer;
    private $crmId;
    private $additionalInformation;
    private $infoCallId;
    private $affiliationId;
    private $voucherId;
    private $partnerId;
    private $selectedPartnerId;
    /** @var Partner */
    private $selectedPartner;
    /** @var Collection */
    private $partnersFromList;
    private $businessId;
    private $vendorId;
    private $categoryAnswers;
    /** @var Location */
    private $location;
    /** @var PriceCalculation */
    private $priceCalculation;
    /** @var DiscountCalculation */
    private $discountCalculation;
    /** @var OrderVoucherData */
    private $orderVoucherData;
    /** @var PartnerListBuilder */
    private $partnerListBuilder;
    /** @var Director */
    private $partnerListDirector;
    /** @var ServiceRequest */
    private $serviceRequest;
    /** @var ServiceRequestObject[] */
    private $serviceRequestObject;
    /** @var Creator */
    private $partnerOrderRequestCreator;
    private $orderRequestAlgorithm;

    public function __construct(Creator $creator, PriceCalculation $priceCalculation, DiscountCalculation $discountCalculation, OrderVoucherData $orderVoucherData,
                                PartnerListBuilder $partnerListBuilder, Director $director, ServiceRequest $serviceRequest, OrderRequestAlgorithm $orderRequestAlgorithm)
    {
        $this->priceCalculation = $priceCalculation;
        $this->discountCalculation = $discountCalculation;
        $this->orderVoucherData = $orderVoucherData;
        $this->partnerListBuilder = $partnerListBuilder;
        $this->partnerListDirector = $director;
        $this->serviceRequest = $serviceRequest;
        $this->partnerOrderRequestCreator = $creator;
        $this->orderRequestAlgorithm = $orderRequestAlgorithm;
    }

    /**
     * @param mixed $deliveryAddressId
     * @return OrderPlace
     */
    public function setDeliveryAddressId($deliveryAddressId)
    {
        $this->deliveryAddressId = $deliveryAddressId;
        $this->setDeliveryAddress();
        return $this;
    }

    /**
     * @param $services
     * @return $this
     * @throws ValidationException
     */
    public function setServices($services)
    {
        $this->serviceRequestObject = $this->serviceRequest->setServices(json_decode($services, 1))->get();
        $this->category = $this->getCategory();
        return $this;
    }

    /**
     * @param mixed $salesChannel
     * @return OrderPlace
     */
    public function setSalesChannel($salesChannel)
    {
        $this->salesChannel = $salesChannel;
        return $this;
    }

    /**
     * @param mixed $deliveryMobile
     * @return OrderPlace
     */
    public function setDeliveryMobile($deliveryMobile)
    {
        $this->deliveryMobile = formatMobile(trim($deliveryMobile));
        return $this;
    }

    /**
     * @param mixed $deliveryName
     * @return OrderPlace
     */
    public function setDeliveryName($deliveryName)
    {
        $this->deliveryName = trim($deliveryName);
        return $this;
    }

    /**
     * @param mixed $paymentMethod
     * @return OrderPlace
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @param mixed $scheduleDate
     * @return OrderPlace
     */
    public function setScheduleDate($scheduleDate)
    {
        $this->scheduleDate = $scheduleDate;
        return $this;
    }

    /**
     * @param mixed $scheduleTime
     * @return OrderPlace
     */
    public function setScheduleTime($scheduleTime)
    {
        $this->scheduleTime = $scheduleTime;
        return $this;
    }

    /**
     * @param mixed $customer
     * @return OrderPlace
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param mixed $crmId
     * @return OrderPlace
     */
    public function setCrmId($crmId)
    {
        $this->crmId = $crmId;
        return $this;
    }

    /**
     * @param mixed $additionalInformation
     * @return OrderPlace
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;
        return $this;
    }

    /**
     * @param mixed $infoCallId
     * @return OrderPlace
     */
    public function setInfoCallId($infoCallId)
    {
        $this->infoCallId = $infoCallId;
        return $this;
    }

    /**
     * @param mixed $affiliationId
     * @return OrderPlace
     */
    public function setAffiliationId($affiliationId)
    {
        $this->affiliationId = $affiliationId;
        return $this;
    }

    /**
     * @param mixed $voucherId
     * @return OrderPlace
     */
    public function setVoucherId($voucherId)
    {
        $this->voucherId = $voucherId;
        return $this;
    }

    /**
     * @param mixed $partnerId
     * @return OrderPlace
     */
    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;

    }

    public function setSelectedPartnerId($partnerId)
    {
        $this->selectedPartnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $businessId
     * @return OrderPlace
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
        return $this;
    }

    /**
     * @param mixed $vendorId
     * @return OrderPlace
     */
    public function setVendorId($vendorId)
    {
        $this->vendorId = $vendorId;
        return $this;
    }

    /**
     * @param mixed $categoryAnswers
     * @return OrderPlace
     */
    public function setCategoryAnswers($categoryAnswers)
    {
        $this->categoryAnswers = $categoryAnswers;
        return $this;
    }

    private function setDeliveryAddress()
    {
        $this->deliveryAddress = $this->customer->delivery_addresses()->withTrashed()->where('id', $this->deliveryAddressId)->first();
        if ($this->deliveryAddress->mobile != $this->deliveryMobile) {
            $new_address = $this->deliveryAddress->replicate();
            $new_address->mobile = $this->deliveryMobile;
            $new_address->name = $this->deliveryName;
            $this->deliveryAddress = $this->customer->delivery_addresses()->save($new_address);
        }
        $this->setLocation();
    }

    private function getCategory()
    {
        return $this->serviceRequestObject[0]->getCategory();
    }

    private function setLocation()
    {
        $hyper_local = HyperLocal::insidePolygon($this->deliveryAddress->geo->lat, $this->deliveryAddress->geo->lng)->with('location')->first();
        $this->location = $hyper_local->location;
    }

    /**
     * @param $services
     * @return ServiceObject[]|Collection
     */
    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $service = $this->category->isRentCar() ? new RentACarServiceObject($service) : new ServiceObject($service);
            $selected_services->push($service);
        }
        return $selected_services;
    }

    public function create()
    {
        try {
            $this->fetchPartner();
            $job_services = $this->createJobService();
            $this->setVoucherData($job_services);
            $order = null;
            DB::transaction(function () use ($job_services, &$order) {
                $order = $this->createOrder();
                $partner_order = $this->createPartnerOrder($order);
                $job = $this->createJob($partner_order);
                $this->createCarRentalDetail($job);
                $job->jobServices()->saveMany($job_services);
                if ($this->canCreatePartnerOrderRequest()) {
                    $partners = $this->orderRequestAlgorithm->setCustomer($this->customer)->setPartners($this->partnersFromList)->getPartners();
                    $this->partnerOrderRequestCreator->setPartnerOrder($partner_order)->setPartners($partners->pluck('id')->toArray())->create();
                }
            });
        } catch (QueryException $e) {
            throw $e;
        }
        return $order;
    }

    private function fetchPartner()
    {
        $geo = new Geo();
        $geo->setLng($this->deliveryAddress->geo->lng)->setLat($this->deliveryAddress->geo->lat);
        $this->partnerListBuilder->setGeo($geo)->setServiceRequestObjectArray($this->serviceRequestObject)
            ->setScheduleTime($this->scheduleTime)->setScheduleDate($this->scheduleDate);
        if ($this->selectedPartnerId) $this->partnerListBuilder->setPartnerIds([$this->selectedPartnerId]);
        $this->partnerListDirector->setBuilder($this->partnerListBuilder)->buildPartnerListForOrderPlacement();
        $this->partnersFromList = $this->partnerListBuilder->get();
        if ($this->selectedPartnerId) $this->selectedPartner = $this->partnersFromList->first();
    }

    private function createJobService()
    {
        $job_services = collect();
        foreach ($this->serviceRequestObject as $selected_service) {
            /** @var ServiceRequestObject $selected_service */
            $service = $selected_service->getService();
            $location_service = LocationService::where([['service_id', $service->id], ['location_id', $this->location->id]])->first();
            $this->priceCalculation->setLocationService($location_service)->setOption($selected_service->getOption());
            $unit_price = $this->priceCalculation->getUnitPrice();
            $this->discountCalculation->setLocationService($location_service)->setOriginalPrice($unit_price * $selected_service->getQuantity())->calculate();
            $service_data = [
                'service_id' => $service->id,
                'quantity' => $selected_service->getQuantity(),
                'unit_price' => $unit_price,
                'min_price' => 0,
                'sheba_contribution' => $this->discountCalculation->getShebaContribution(),
                'partner_contribution' => $this->discountCalculation->getPartnerContribution(),
                'location_service_discount_id' => $this->discountCalculation->getDiscountId(),
                'discount' => $this->discountCalculation->getDiscountedPrice(),
                'discount_percentage' => $this->discountCalculation->getIsDiscountPercentage() ? $this->discountCalculation->getDiscount() : 0,
                'name' => $service->name,
                'variable_type' => $service->variable_type,
                'surcharge_percentage' => 0
            ];
            list($service_data['option'], $service_data['variables']) = $service->getVariableAndOption($selected_service->getOption());
            $job_services->push(new JobService($service_data));
        }
        return $job_services;
    }

    private function setVoucherData($job_services)
    {
        $order_amount = $job_services->map(function ($job_service) {
                return $job_service->unit_price * $job_service->quantity;
            })->sum() + 0;
        if ($this->voucherId) {
            $result = voucher($this->voucherId)->check($this->category->id, null, $this->location->id, $this->customer->id, $order_amount, $this->salesChannel)->reveal();
            $this->orderVoucherData->setVoucherRevealData($result);
        }

    }

    private function createOrder()
    {
        $order = new Order();
        $order->info_call_id = $this->_setInfoCallId();
        $order->affiliation_id = $this->_setAffiliationId();
        $order->delivery_mobile = $this->deliveryMobile;
        $order->delivery_name = $this->deliveryName;
        $order->sales_channel = $this->salesChannel;
        $order->location_id = $this->deliveryAddress->location_id;
        $order->customer_id = $this->customer->id;
        $order->voucher_id = $this->orderVoucherData->isValid() ? $this->orderVoucherData->getVoucherId() : null;
        $order->partner_id = $this->partnerId;
        $order->business_id = $this->businessId;
        $order->vendor_id = $this->vendorId;
        $order->delivery_address_id = $this->deliveryAddress->id;
        $order->fill((new RequestIdentification())->get());
        $this->withCreateModificationField($order);
        $order->save();
        return $order;
    }

    private function _setInfoCallId()
    {
        if ($this->infoCallId) {
            $info_call = InfoCall::whereDoesntHave('order')->where('id', $this->infoCallId)->first();
            if ($info_call) return $this->infoCallId;
        }
        return null;
    }

    private function _setAffiliationId()
    {
        if ($this->affiliationId) {
            $affiliation = Affiliation::whereDoesntHave('order')->where('id', $this->affiliationId)->first();
            if ($affiliation) return $this->affiliationId;
        }
        return null;
    }

    private function createPartnerOrder(Order $order)
    {
        $partner_order = new PartnerOrder();
        $partner_order->order_id = $order->id;
        $partner_order->payment_method = $this->paymentMethod;
        $partner_order->partner_id = $this->selectedPartner ? $this->selectedPartner->id : null;
        $this->withCreateModificationField($partner_order);
        $partner_order->save();
        return $partner_order;
    }

    private function createJob(PartnerOrder $partner_order)
    {
        $preferred_time = new PreferredTime($this->scheduleTime);
        $job_data = [
            'category_id' => $this->category->id,
            'partner_order_id' => $partner_order->id,
            'schedule_date' => $this->scheduleDate,
            'preferred_time' => $preferred_time->toString(),
            'preferred_time_start' => $preferred_time->getStartString(),
            'preferred_time_end' => $preferred_time->getEndString(),
            'crm_id' => $this->crmId,
            'job_additional_info' => $this->additionalInformation,
            'category_answers' => $this->categoryAnswers,
            'material_commission_rate' => config('sheba.material_commission_rate'),
            'status' => JobStatuses::PENDING,
        ];
        if ($this->orderVoucherData->isValid()) {
            $job_data['discount'] = $this->orderVoucherData->getDiscount();
            $job_data['sheba_contribution'] = $this->orderVoucherData->getShebaContribution();
            $job_data['partner_contribution'] = $this->orderVoucherData->getPartnerContribution();
            $job_data['discount_percentage'] = $this->orderVoucherData->getDiscountPercentage();
        }
        $job_data = $this->withCreateModificationField($job_data);
        return Job::create($job_data);
    }

    private function createCarRentalDetail(Job $job)
    {
        if (!$this->category->isRentCar()) return;
        $service = $this->services->first();
        $car_rental_detail = new CarRentalJobDetail();
        $car_rental_detail->pick_up_location_id = $service->pickUpLocationId;
        $car_rental_detail->pick_up_location_type = $service->pickUpLocationType;
        $car_rental_detail->pick_up_address_geo = json_encode(array('lat' => $service->pickUpLocationLat, 'lng' => $service->pickUpLocationLng));
        $car_rental_detail->pick_up_address = $service->pickUpAddress;
        $car_rental_detail->destination_location_id = $service->destinationLocationId;
        $car_rental_detail->destination_location_type = $service->destinationLocationType;
        $car_rental_detail->destination_address_geo = json_encode(array('lat' => $service->destinationLocationLat, 'lng' => $service->destinationLocationLng));
        $car_rental_detail->destination_address = $service->destinationAddress;
        $car_rental_detail->drop_off_date = $service->dropOffDate;
        $car_rental_detail->drop_off_time = $service->dropOffTime;
        $car_rental_detail->estimated_distance = $service->estimatedDistance;
        $car_rental_detail->estimated_time = $service->estimatedTime;
        $car_rental_detail->job_id = $job->id;
        $this->withCreateModificationField($car_rental_detail);
        $car_rental_detail->save();
    }

    private function canCreatePartnerOrderRequest()
    {

        return !$this->selectedPartner || count($this->partnersFromList) > 0;
    }
}
