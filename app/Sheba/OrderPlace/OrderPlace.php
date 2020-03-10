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
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Dal\JobService\JobService;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\Jobs\JobStatuses;
use Sheba\Jobs\PreferredTime;
use Sheba\Location\Geo;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\ModificationFields;
use Sheba\OrderPlace\Exceptions\LocationIdNullException;
use Sheba\Partner\ImpressionManager;
use Sheba\PartnerList\Director;
use Sheba\PartnerList\PartnerListBuilder;
use Sheba\PartnerOrderRequest\Creator;
use Sheba\PartnerOrderRequest\Store;
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
    private $address;
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
    /** @var UpsellCalculation */
    private $upsellCalculation;
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
    /** @var Store */
    private $orderRequestStore;
    /**
     * @var OrderRequestAlgorithm
     */
    private $orderRequestAlgorithm;
    /** @var JobDiscountHandler $jobDiscountHandler */
    private $jobDiscountHandler;
    /** @var float */
    private $orderAmount;
    /** @var float */
    private $orderAmountWithoutDeliveryCharge;
    /** @var ImpressionManager */
    private $impressionManager;

    public function __construct(Creator $creator, PriceCalculation $priceCalculation, DiscountCalculation $discountCalculation, OrderVoucherData $orderVoucherData,
                                PartnerListBuilder $partnerListBuilder, Director $director, ServiceRequest $serviceRequest,
                                OrderRequestAlgorithm $orderRequestAlgorithm, JobDiscountHandler $job_discount_handler, UpsellCalculation $upsell_calculation, Store $order_request_store, ImpressionManager $impressionManager)
    {
        $this->priceCalculation = $priceCalculation;
        $this->discountCalculation = $discountCalculation;
        $this->orderVoucherData = $orderVoucherData;
        $this->partnerListBuilder = $partnerListBuilder;
        $this->partnerListDirector = $director;
        $this->serviceRequest = $serviceRequest;
        $this->partnerOrderRequestCreator = $creator;
        $this->orderRequestAlgorithm = $orderRequestAlgorithm;
        $this->jobDiscountHandler = $job_discount_handler;
        $this->upsellCalculation = $upsell_calculation;
        $this->orderRequestStore = $order_request_store;
        $this->impressionManager = $impressionManager;
    }


    /**
     * @param $deliveryAddressId
     * @return $this
     */
    public function setDeliveryAddressId($deliveryAddressId)
    {
        $this->deliveryAddressId = $deliveryAddressId;
        return $this;
    }

    /**
     * @param $delivery_address
     * @return $this
     */
    public function setDeliveryAddress($delivery_address)
    {
        $this->address = $delivery_address;
        return $this;
    }

    /**
     * @param $services
     * @return $this
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
     * @param $deliveryMobile
     * @return $this
     */
    public function setDeliveryMobile($deliveryMobile)
    {
        $this->deliveryMobile = formatMobile(trim($deliveryMobile));
        return $this;
    }

    /**
     * @param $deliveryName
     * @return $this
     */
    public function setDeliveryName($deliveryName)
    {
        $this->deliveryName = trim($deliveryName);
        return $this;
    }

    /**
     * @param $paymentMethod
     * @return $this
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
     * @param $customer
     * @return $this
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
     * @param $affiliationId
     * @return $this
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

    /**
     * @param $partnerId
     * @return $this
     */
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

    private function setDeliveryAddressFromId()
    {
        $this->deliveryAddress = $this->customer->delivery_addresses()->withTrashed()->where('id', $this->deliveryAddressId)->first();
        if ($this->deliveryAddress->mobile != $this->deliveryMobile) {
            $new_address = $this->deliveryAddress->replicate();
            $new_address->mobile = $this->deliveryMobile;
            $new_address->name = $this->deliveryName;
            $new_address = $this->customer->delivery_addresses()->save($new_address);
            $this->setCustomerDeliveryAddress($new_address);
        }
        $hyper_local = HyperLocal::insidePolygon($this->deliveryAddress->geo->lat, $this->deliveryAddress->geo->lng)->with('location')->first();
        $this->setLocation($hyper_local->location);
    }

    /**
     * @return Category
     */
    private function getCategory()
    {
        return $this->serviceRequestObject[0]->getCategory();
    }

    /**
     * @param Location $location
     */
    private function setLocation(Location $location)
    {
        $this->location = $location;
    }

    /**
     * @return null
     * @throws Exception
     */
    public function create()
    {
        try {
            $this->resolveAddress();
            $this->fetchPartner();
            $job_services = $this->createJobService();
            $this->calculateOrderAmount($job_services);
            $this->setVoucherData();
            $order = null;
            DB::transaction(function () use ($job_services, &$order) {
                $order = $this->createOrder();
                $partner_order = $this->createPartnerOrder($order);
                $job = $this->createJob($partner_order);
                $this->createCarRentalDetail($job);
                $job->jobServices()->saveMany($job_services);
                if ($this->jobDiscountHandler->hasDiscount()) $this->jobDiscountHandler->create($job);
                $this->updateVoucherInPromoList($order);
                if (!$order->location_id) throw new LocationIdNullException("Order #" . $order->id . " has no location id");
                if ($this->canCreatePartnerOrderRequest()) {
                    $partners = $this->orderRequestAlgorithm->setCustomer($this->customer)->setPartners($this->partnersFromList)->getPartners();
                    $this->impressionManager->setLocationId($this->location->id)->setCategoryId($this->category->id)->setCustomerId($this->customer->id)
                        ->setServiceRequestObject($this->serviceRequestObject)->deduct($partners->pluck('id')->values()->all());
                    $this->orderRequestStore->setPartnerOrderId($partner_order->id)->setPartners($partners->pluck('id')->values()->all())->set();
                    $this->partnerOrderRequestCreator->setPartnerOrder($partner_order)->setPartners([$partners->first()->id])->create();
                }
            });
        } catch (QueryException $e) {
            throw $e;
        }
        return $order;
    }

    private function resolveAddress()
    {
        if ($this->deliveryAddressId) $this->setDeliveryAddressFromId();
        if ($this->deliveryAddress) return;
        $address = new CustomerDeliveryAddress();
        $address->name = $this->address;
        $address->mobile = $this->deliveryMobile;
        $service = $this->serviceRequestObject[0];
        $lat = $service->getPickUpGeo()->getLat();
        $lng = $service->getPickUpGeo()->getLng();
        $hyper_local = HyperLocal::insidePolygon($lat, $lng)->with('location')->first();
        $address->geo_informations = json_encode(['lat' => $lat, 'lng' => $lng]);
        $address->location_id = $hyper_local->location->id;
        $address->customer_id = $this->customer->id;
        $this->withCreateModificationField($address);
        $address->save();
        $this->setCustomerDeliveryAddress($address);
        $this->setLocation($hyper_local->location);
    }

    /**
     * @param CustomerDeliveryAddress $address
     * @return $this
     */
    private function setCustomerDeliveryAddress(CustomerDeliveryAddress $address)
    {
        $this->deliveryAddress = $address;
        return $this;
    }

    private function fetchPartner()
    {
        $geo = new Geo();
        $geo->setLng($this->deliveryAddress->geo->lng)->setLat($this->deliveryAddress->geo->lat);
        $this->partnerListBuilder
            ->setGeo($geo)
            ->setServiceRequestObjectArray($this->serviceRequestObject)
            ->setScheduleTime($this->scheduleTime)
            ->setScheduleDate($this->scheduleDate);

        if ($this->selectedPartnerId) $this->partnerListBuilder->setPartnerIds([$this->selectedPartnerId]);
        $this->partnerListDirector->setBuilder($this->partnerListBuilder)->buildPartnerListForOrderPlacement();
        $this->partnersFromList = $this->partnerListBuilder->get();
        if ($this->selectedPartnerId) $this->selectedPartner = $this->partnersFromList->first();
    }

    private function createJobService()
    {
        $job_services = collect();
        foreach ($this->serviceRequestObject as $selected_service) {
            $service = $selected_service->getService();
            $location_service = LocationService::where([['service_id', $service->id], ['location_id', $this->location->id]])->first();
            $this->priceCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())->setQuantity($selected_service->getQuantity());
            $upsell_unit_price = $this->upsellCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())
                ->setQuantity($selected_service->getQuantity())->getUpsellUnitPriceForSpecificQuantity();
            $unit_price = $upsell_unit_price ? $upsell_unit_price : $this->priceCalculation->getUnitPrice();
            $total_original_price = $this->category->isRentACar() ? $this->priceCalculation->getTotalOriginalPrice() : $unit_price * $selected_service->getQuantity();
            $this->discountCalculation->setLocationService($location_service)->setOriginalPrice($total_original_price)->setQuantity($selected_service->getQuantity())->calculate();
            $service_data = [
                'service_id' => $service->id,
                'quantity' => $selected_service->getQuantity(),
                'unit_price' => $unit_price,
                'min_price' => $this->priceCalculation->getMinPrice(),
                'sheba_contribution' => $this->discountCalculation->getShebaContribution(),
                'partner_contribution' => $this->discountCalculation->getPartnerContribution(),
                'location_service_discount_id' => $this->discountCalculation->getDiscountId(),
                'discount' => $this->discountCalculation->getJobServiceDiscount(),
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

    /**
     * @throws Exception
     */
    private function setVoucherData()
    {
        if ($this->voucherId) {
            $result = voucher($this->voucherId)->check($this->category->id, null, $this->location->id, $this->customer->id, $this->orderAmountWithoutDeliveryCharge, $this->salesChannel)->reveal();
            $this->orderVoucherData->setVoucherRevealData($result);
        }
    }

    /**
     * @return Order
     */
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

    /**
     * @param Order $order
     * @return PartnerOrder
     */
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

    /**
     * @param PartnerOrder $partner_order
     * @return Job
     * @throws InvalidDiscountType
     */
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

        if ($this->selectedPartner) $job_data['commission_rate'] = $this->category->commission($this->selectedPartner->id);

        $job_data['discount'] = 0.00;
        if ($this->orderVoucherData->isValid()) {
            $job_data['discount'] = $this->orderVoucherData->getDiscount();
            $job_data['sheba_contribution'] = $this->orderVoucherData->getShebaContribution();
            $job_data['partner_contribution'] = $this->orderVoucherData->getPartnerContribution();
            $job_data['discount_percentage'] = $this->orderVoucherData->getDiscountPercentage();
            $job_data['original_discount_amount'] = $this->orderVoucherData->getOriginalDiscountAmount();
        }
        $this->handleDelivery($job_data);
        $job_data = $this->withCreateModificationField($job_data);

        return Job::create($job_data);
    }

    /**
     * @param $job_data
     * @throws InvalidDiscountType
     */
    private function handleDelivery(&$job_data)
    {
        $delivery_charge = $this->buildDeliveryCharge();
        $charge = $delivery_charge->get();
        $job_data['delivery_charge'] = $delivery_charge->doesUseShebaLogistic() ? 0 : $charge;
        $job_data['logistic_charge'] = $delivery_charge->doesUseShebaLogistic() ? $charge : 0;
        if ($delivery_charge->doesUseShebaLogistic()) {
            $job_data['needs_logistic'] = 1;
            $job_data['logistic_parcel_type'] = $this->category->logistic_parcel_type;
            $job_data['logistic_nature'] = $this->category->logistic_nature;
            $job_data['one_way_logistic_init_event'] = $this->category->one_way_logistic_init_event;
        }
        $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($charge)->setOrderAmount($this->orderAmount);
        $this->jobDiscountHandler->setType(DiscountTypes::DELIVERY)->setCategory($this->category)->setCheckingParams($discount_checking_params)->calculate();

        if ($this->jobDiscountHandler->hasDiscount()) {
            $job_data['discount'] += $this->jobDiscountHandler->getApplicableAmount();
        }
    }

    private function buildDeliveryCharge()
    {
        return (new DeliveryCharge())->setCategory($this->category);
    }

    /**
     * @param Job $job
     */
    private function createCarRentalDetail(Job $job)
    {
        if (!$this->category->isRentCar()) return;
        /** @var ServiceRequestObject $service */
        $service = $this->serviceRequestObject[0];
        $car_rental_detail = new CarRentalJobDetail();
        $pickup_thana = $service->getPickupThana();
        $destination_thana = $service->getDestinationThana();
        $car_rental_detail->pick_up_location_id = $pickup_thana->id;
        $car_rental_detail->pick_up_location_type = "App\\Models\\" . class_basename($pickup_thana);
        $car_rental_detail->pick_up_address_geo = json_encode(array('lat' => $service->getPickUpGeo()->getLat(), 'lng' => $service->getPickUpGeo()->getLng()));
        $car_rental_detail->pick_up_address = $service->getPickUpAddress();
        if ($destination_thana) {
            $car_rental_detail->destination_location_id = $destination_thana->id;
            $car_rental_detail->destination_location_type = "App\\Models\\" . class_basename($destination_thana);
            $car_rental_detail->destination_address_geo = json_encode(array('lat' => $service->getDestinationGeo()->getLat(), 'lng' => $service->getDestinationGeo()->getLng()));
            $car_rental_detail->destination_address = $service->getDestinationAddress();
        }
        $car_rental_detail->drop_off_date = $service->getDropOffDate();
        $car_rental_detail->drop_off_time = $service->getDropOffTime();
        $car_rental_detail->estimated_distance = $service->getEstimatedDistance();
        $car_rental_detail->estimated_time = $service->getEstimatedTime();
        $car_rental_detail->job_id = $job->id;
        $this->withCreateModificationField($car_rental_detail);
        $car_rental_detail->save();
    }

    /**
     * @return bool
     */
    private function canCreatePartnerOrderRequest()
    {
        return !$this->selectedPartner && count($this->partnersFromList) > 0;
    }

    /**
     * @param Order $order
     */
    private function updateVoucherInPromoList(Order $order)
    {
        if (!$order->voucher_id) return;
        $rules = json_decode($order->voucher->rules);
        if (array_key_exists('nth_orders', $rules) && !array_key_exists('ignore_nth_orders_if_used', $rules)) {
            if ($this->customer->orders->count() == max($rules->nth_orders)) $this->customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
        }
        if ($order->voucher->usage($this->customer->profile) == $order->voucher->max_order) $this->customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
    }

    /**
     * @param $job_services
     */
    private function calculateOrderAmount($job_services)
    {
        $this->orderAmountWithoutDeliveryCharge = $job_services->map(function ($job_service) {
            return $job_service->unit_price * $job_service->quantity;
        })->sum();
        $this->orderAmount = $this->orderAmountWithoutDeliveryCharge + (double)$this->category->delivery_charge;
    }
}
