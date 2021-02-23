<?php namespace Sheba\Resource\Jobs\Service;


use App\Exceptions\NotFoundException;
use App\Exceptions\ServiceRequest\MultipleCategoryServiceRequestException;
use Sheba\Dal\Category\Category;
use App\Models\Job;
use Sheba\Dal\LocationService\LocationService;
use App\Models\Order;
use App\Models\PartnerOrder;
use Sheba\Dal\JobService\JobServiceRepositoryInterface;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\PriceCalculation\PriceCalculationFactory;
use Sheba\ServiceRequest\ServiceRequestObject;
use Sheba\Dal\JobService\JobService;

class Creator
{
    /** @var Job */
    private $job;
    /** @var  JobService[] */
    private $jobServices;
    /** @var PartnerOrder */
    private $partnerOrder;
    /** @var Order */
    private $order;
    /** @var ServiceRequestObject[] */
    private $services;
    private $priceCalculation;
    private $upsellCalculation;
    private $discountCalculation;
    /** @var JobServiceRepositoryInterface */
    private $jobServiceRepository;
    /** @var ServiceUpdateRequestPolicy */
    private $policy;

    public function __construct(ServiceUpdateRequestPolicy $policy, UpsellCalculation $upsellCalculation, DiscountCalculation $discountCalculation, JobServiceRepositoryInterface $jobServiceRepository)
    {
        $this->policy = $policy;
        $this->upsellCalculation = $upsellCalculation;
        $this->discountCalculation = $discountCalculation;
        $this->jobServiceRepository = $jobServiceRepository;
    }

    /**
     * @param Job $job
     * @return Creator
     */
    public function setJob($job)
    {
        $this->job = $job;
        $this->setJobServices($this->job->jobServices);
        $this->setPartnerOrder($this->job->partnerOrder);
        $this->setOrder($this->job->partnerOrder->order);
        $this->policy->setJob($this->job);
        return $this;
    }

    /**
     * @param JobService[] $jobServices
     * @return Creator
     */
    private function setJobServices($jobServices)
    {
        $this->jobServices = $jobServices;
        return $this;
    }

    /**
     * @param ServiceRequestObject[] $services
     * @return Creator
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @param PartnerOrder $partnerOrder
     * @return Creator
     */
    private function setPartnerOrder($partnerOrder)
    {
        $this->partnerOrder = $partnerOrder;
        return $this;
    }

    /**
     * @param Order $order
     * @return Creator
     */
    private function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    public function create()
    {
        foreach ($this->services as $selected_service) {
            if ($selected_service->getCategory()->id != $this->job->category_id) throw new MultipleCategoryServiceRequestException();
            if ($this->policy->existInJob($selected_service)) throw new ServiceExistsInOrderException();
            $service = $selected_service->getService();
            $location_service = LocationService::where([['service_id', $service->id], ['location_id', $this->order->deliveryAddress->location_id]])->first();
            if (!$selected_service->getCategory()->isRentACarOutsideCity() && !$location_service) throw new NotFoundException('Service #' . $service->id . ' is not available at this location #' . $this->order->deliveryAddress->location_id);
            $this->priceCalculation = $this->resolvePriceCalculation($selected_service->getCategory());
            $this->priceCalculation->setService($service)->setOption($selected_service->getOption())->setQuantity($selected_service->getQuantity());
            $selected_service->getCategory()->isRentACarOutsideCity() ? $this->priceCalculation->setPickupThanaId($selected_service->getPickupThana()->id)->setDestinationThanaId($selected_service->getDestinationThana()->id) : $this->priceCalculation->setLocationService($location_service);
            $upsell_unit_price = $this->upsellCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())
                ->setQuantity($selected_service->getQuantity())->getUpsellUnitPriceForSpecificQuantity();
            if ($upsell_unit_price) $this->priceCalculation->setUpsellUnitPrice($upsell_unit_price);
            $unit_price = $upsell_unit_price ? $upsell_unit_price : $this->priceCalculation->getUnitPrice();
            $total_original_price = $this->priceCalculation->getTotalOriginalPrice();
            $this->discountCalculation->setService($service)->setLocationService($location_service)->setOriginalPrice($unit_price*$selected_service->getQuantity())->calculate();
            $service_data = [
                'service_id' => $service->id,
                'job_id' => $this->job->id,
                'quantity' => $selected_service->getQuantity(),
                'unit_price' => $unit_price,
                'min_price' => $selected_service->getCategory()->isRentACarOutsideCity() ? 0 : $this->priceCalculation->getMinPrice(),
                'discount' => $this->discountCalculation->getJobServiceDiscount(),
                'sheba_contribution' => $this->discountCalculation->getShebaContribution(),
                'partner_contribution' => $this->discountCalculation->getPartnerContribution(),
                'location_service_discount_id' => $this->discountCalculation->getDiscountId(),
                'name' => $service->name,
                'variable_type' => $service->variable_type,
                'surcharge_percentage' => $this->priceCalculation->getSurcharge()
            ];
            if (!$this->order->hasVoucher()) {
                $this->discountCalculation->setService($service)->setLocationService($location_service)->setOriginalPrice($total_original_price)->setQuantity($selected_service->getQuantity())->calculate();
                $service_data['discount'] = $this->discountCalculation->getJobServiceDiscount();
                $service_data['discount_percentage'] = $this->discountCalculation->getIsDiscountPercentage() ? $this->discountCalculation->getDiscount() : 0;
            }
            list($service_data['option'], $service_data['variables']) = $service->getVariableAndOption($selected_service->getOption());
            $this->jobServiceRepository->create($service_data);
        }
    }

    private function resolvePriceCalculation(Category $category)
    {
        $priceCalculationFactory = new PriceCalculationFactory();
        $priceCalculationFactory->setCategory($category);
        return $priceCalculationFactory->get();
    }


}
