<?php namespace Sheba\Services;


use App\Exceptions\HyperLocationNotFoundException;
use App\Exceptions\NotFoundException;
use Sheba\Dal\Category\Category;
use App\Models\HyperLocal;
use Sheba\Dal\LocationService\LocationService;
use Illuminate\Support\Collection;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\PriceCalculation\PriceCalculationFactory;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\ServiceRequest\ServiceRequestObject;

class ServicePriceCalculation
{
    /** @var Category */
    private $category;
    /** @var Collection */
    private $services;
    protected $serviceRequest;
    /** @var ServiceRequestObject[] */
    private $serviceRequestObject;
    private $location;
    private $priceCalculation;
    /** @var UpsellCalculation */
    private $upsellCalculation;
    /** @var DiscountCalculation */
    private $discountCalculation;
    /** @var float */
    private $orderAmountWithoutDeliveryCharge;
    /** @var float */
    private $orderTotalDiscount;
    private $delivery_charge;
    private $job_discount_handler;

    public function __construct(ServiceRequest $serviceRequest, UpsellCalculation $upsell_calculation, DiscountCalculation $discountCalculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler)
    {
        $this->serviceRequest = $serviceRequest;
        $this->discountCalculation = $discountCalculation;
        $this->upsellCalculation = $upsell_calculation;
        $this->delivery_charge = $delivery_charge;
        $this->job_discount_handler = $job_discount_handler;
    }

    public function setLocation($lat, $lng)
    {
        $hyper_local = HyperLocal::insidePolygon($lat, $lng)->with('location')->first();
        if (!$hyper_local) throw new HyperLocationNotFoundException('Your are out of service area.');
        $this->location = $hyper_local->location;
        return $this;
    }

    public function setServices($services)
    {
        $this->serviceRequestObject = $this->serviceRequest->setServices(json_decode($services, 1))->get();
        $this->category = $this->getCategory();
        return $this;
    }

    public function getCalculatedPrice()
    {
        $services_list = $this->createServiceList();
        $this->calculateOrderAmount($services_list);
        $this->calculateTotalDiscount($services_list);
        $category = $this->calculateDeliveryCharge();
        $price = [];
        $price['total_original_price'] = $this->orderAmountWithoutDeliveryCharge;
        $price['total_discounted_price'] = $this->orderAmountWithoutDeliveryCharge - $this->orderTotalDiscount;
        $price['total_discount'] = $this->orderTotalDiscount;
        $price['delivery_charge'] = $category['delivery_charge'];
        $price['delivery_discount'] = $category['delivery_discount'];
        $price['breakdown'] = [];
        foreach ($services_list as $key => $service) {
            $price['breakdown'][$key]['id'] = $service['service_id'];
            $price['breakdown'][$key]['quantity'] = $service['quantity'];
            $price['breakdown'][$key]['unit_price'] = $service['unit_price'];
            $price['breakdown'][$key]['discount'] = $service['discount'];
            $price['breakdown'][$key]['option'] = json_decode($service['option']);
            $price['breakdown'][$key]['variables'] = json_decode($service['variables']);
            $price['breakdown'][$key]['original_price'] =$service['original_price'];
            $price['breakdown'][$key]['discounted_price'] = $service['discounted_price'];
        }
        return $price;
    }

    /**
     * @return Category
     */
    private function getCategory()
    {
        return $this->serviceRequestObject[0]->getCategory();
    }

    public function createServiceList()
    {
        $services_list = collect();
        foreach ($this->serviceRequestObject as $selected_service) {
            $service = $selected_service->getService();
            $this->priceCalculation = $this->resolvePriceCalculation($service->category);
            $location_service = LocationService::where([['service_id', $service->id], ['location_id', $this->location->id]])->first();
            if (!$this->category->isRentACarOutsideCity() && !$location_service) throw new NotFoundException('Service #' . $service->id . ' is not available at this location', 403);
            $this->priceCalculation->setService($service)->setOption($selected_service->getOption())->setQuantity($selected_service->getQuantity());
            $this->category->isRentACarOutsideCity() ? $this->priceCalculation->setPickupThanaId($selected_service->getPickupThana()->id)->setDestinationThanaId($selected_service->getDestinationThana()->id) : $this->priceCalculation->setLocationService($location_service);
            $upsell_unit_price = $this->upsellCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())
                ->setQuantity($selected_service->getQuantity())->getUpsellUnitPriceForSpecificQuantity();
            if($upsell_unit_price) $this->priceCalculation->setUpsellUnitPrice($upsell_unit_price);
            $unit_price = $upsell_unit_price ? $upsell_unit_price : $this->priceCalculation->getUnitPrice();
            $total_original_price = $this->priceCalculation->getTotalOriginalPrice();
            $this->discountCalculation->setService($service)->setLocationService($location_service)->setOriginalPrice($total_original_price)->setQuantity($selected_service->getQuantity())->calculate();
            $service_data = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'variable_type' => $service->variable_type,
                'unit' => $service->unit,
                'quantity' => $selected_service->getQuantity(),
                'unit_price' => $unit_price,
                'discount' => $this->discountCalculation->getJobServiceDiscount(),
                'original_price' => $unit_price * $selected_service->getQuantity(),
                'discounted_price' => ($unit_price * $selected_service->getQuantity()) - $this->discountCalculation->getJobServiceDiscount()
            ];
            list($service_data['option'], $service_data['variables']) = $service->getVariableAndOption($selected_service->getOption());
            $services_list->push($service_data);
        }
        return $services_list;
    }

    /**
     * @param $services_list
     */
    private function calculateOrderAmount($services_list)
    {
        $this->orderAmountWithoutDeliveryCharge = $services_list->map(function ($service) {
            return $service['unit_price'] * $service['quantity'];
        })->sum();
    }

    /**
     * @param $services_list
     */
    private function calculateTotalDiscount($services_list)
    {
        $this->orderTotalDiscount = $services_list->map(function ($service) {
            return $service['discount'];
        })->sum();
    }

    private function calculateDeliveryCharge()
    {
        $category['delivery_charge'] = $this->delivery_charge->setCategory($this->category)->setLocation($this->location)->get();
        /** @var Discount $delivery_discount */
        $delivery_discount = $this->job_discount_handler->getDiscount();
        $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($category['delivery_charge']);
        $this->job_discount_handler->setType(DiscountTypes::DELIVERY)->setCategory($this->category)->setCheckingParams($discount_checking_params)->calculate();
        $delivery_discount = $this->job_discount_handler->getDiscount();
        $category['delivery_discount'] = $delivery_discount ? [
            'value' => (double)$delivery_discount->amount,
            'is_percentage' => $delivery_discount->is_percentage,
            'cap' => (double)$delivery_discount->cap,
            'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
        ] : null;
        return $category;
    }

    private function resolvePriceCalculation(Category $category)
    {
        $priceCalculationFactory = new PriceCalculationFactory();
        $priceCalculationFactory->setCategory($category);
        return $priceCalculationFactory->get();
    }
}
