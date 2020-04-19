<?php namespace Sheba\Resource\Jobs\Service;


use App\Models\Job;
use App\Models\LocationService;
use App\Models\Order;
use App\Models\PartnerOrder;
use Sheba\Dal\JobService\JobServiceRepositoryInterface;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
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

    public function __construct(ServiceUpdateRequestPolicy $policy, PriceCalculation $priceCalculation, UpsellCalculation $upsellCalculation, DiscountCalculation $discountCalculation, JobServiceRepositoryInterface $jobServiceRepository)
    {
        $this->policy = $policy;
        $this->priceCalculation = $priceCalculation;
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
        /** @var ServiceRequestObject $selected_service */
        foreach ($this->services as $selected_service) {
            if ($this->policy->existInJob($selected_service)) throw new ServiceExistsInOrderException('Service already added', 400);
            $service = $selected_service->getService();
            $location_service = LocationService::where([['service_id', $service->id], ['location_id', $this->order->deliveryAddress->location_id]])->first();
            $this->priceCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())->setQuantity($selected_service->getQuantity());
            $upsell_unit_price = $this->upsellCalculation->setService($service)->setLocationService($location_service)->setOption($selected_service->getOption())
                ->setQuantity($selected_service->getQuantity())->getUpsellUnitPriceForSpecificQuantity();
            $unit_price = $upsell_unit_price ? $upsell_unit_price : $this->priceCalculation->getUnitPrice();
            $total_original_price = $this->job->category->isRentACar() ? $this->priceCalculation->getTotalOriginalPrice() : $unit_price * $selected_service->getQuantity();
            $service_data = [
                'service_id' => $service->id,
                'job_id' => $this->job->id,
                'quantity' => $selected_service->getQuantity(),
                'unit_price' => $unit_price,
                'min_price' => $this->priceCalculation->getMinPrice(),
                'sheba_contribution' => $this->discountCalculation->getShebaContribution(),
                'partner_contribution' => $this->discountCalculation->getPartnerContribution(),
                'location_service_discount_id' => $this->discountCalculation->getDiscountId(),
                'name' => $service->name,
                'variable_type' => $service->variable_type,
                'surcharge_percentage' => 0
            ];
            if (!$this->order->hasVoucher()) {
                $this->discountCalculation->setLocationService($location_service)->setOriginalPrice($total_original_price)->setQuantity($selected_service->getQuantity())->calculate();
                $service_data = +[
                    'discount' => $this->discountCalculation->getJobServiceDiscount(),
                    'discount_percentage' => $this->discountCalculation->getIsDiscountPercentage() ? $this->discountCalculation->getDiscount() : 0,
                ];
            }
            list($service_data['option'], $service_data['variables']) = $service->getVariableAndOption($selected_service->getOption());
            $this->jobServiceRepository->create($service_data);
        }
    }


}