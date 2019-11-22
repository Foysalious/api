<?php namespace Sheba\OrderPlace;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\Location;
use App\Models\LocationService;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\Service;
use App\Sheba\Checkout\Discount;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Sheba\Checkout\Services\RentACarServiceObject;
use Sheba\Checkout\Services\ServiceObject;
use Sheba\Dal\JobService\JobService;
use Sheba\Jobs\JobStatuses;
use Sheba\Jobs\PreferredTime;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use DB;

class OrderPlace
{
    use ModificationFields;
    private $deliveryAddressId;
    /** @var CustomerDeliveryAddress */
    private $deliveryAddress;
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

    public function __construct()
    {
        $this->priceCalculation = new PriceCalculation();
        $this->discountCalculation = new DiscountCalculation();
        $this->orderVoucherData = new OrderVoucherData();
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
     * @param mixed $services
     * @return OrderPlace
     */
    public function setServices($services)
    {
        $services = json_decode($services);
        $this->category = $this->getCategory($services);
        $this->services = $this->getSelectedServices($services);
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

    private function getCategory($services)
    {
        return (Service::find((int)$services[0]->id))->category;
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
            $job_services = $this->createJobService();
            $this->setVoucherData($job_services);
            DB::transaction(function () use ($job_services) {
                $order = $this->createOrder();
                $partner_order = $this->createPartnerOrder($order);
                $job = $this->createJob($partner_order);
                $job->jobServices()->saveMany($job_services);
            });
        } catch (QueryException $e) {
            throw $e;
        }
    }

    private function createOrder()
    {
        $order = new Order();
        $order->info_call_id = $this->infoCallId;
        $order->affiliation_id = $this->affiliationId;
        $order->delivery_mobile = $this->deliveryMobile;
        $order->delivery_name = $this->deliveryName;
        $order->sales_channel = $this->salesChannel;
        $order->location_id = $this->deliveryName;
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


    private function createPartnerOrder(Order $order)
    {
        $partner_order = new PartnerOrder();
        $partner_order->order_id = $order->id;
        $partner_order->payment_method = $this->paymentMethod;
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

    private function createJobService()
    {
        $job_services = collect();
        foreach ($this->services as $selected_service) {
            /** @var ServiceObject $selected_service */
            $service = $selected_service->getService();
            $location_service = LocationService::where([['service_id', $service->id], ['location_id', $this->location->id]])->first();
            $this->priceCalculation->setLocationService($location_service)->setOption($selected_service->getOption());
            $unit_price = $this->priceCalculation->getUnitPrice();
            $this->discountCalculation->setLocationService($location_service)->setOriginalPrice($unit_price * $selected_service->quantity)->calculate();
            $service_data = [
                'service_id' => $service->id,
                'quantity' => $selected_service->quantity,
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
            $result = voucher($this->voucherId)->check($this->category->id, $this->partnerId, $this->location->id, $this->customer->id, $order_amount, $this->salesChannel)->reveal();
            $this->orderVoucherData->setVoucherRevealData($result);
        }

    }
}
