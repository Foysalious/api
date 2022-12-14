<?php namespace Sheba\Jobs;


use Sheba\Dal\CategoryPartner\CategoryPartner;
use App\Models\Job;
use App\Models\Partner;
use App\Models\PartnerOrder;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;

class JobDeliveryChargeCalculator
{
    /** @var Job */
    private $job;
    /** @var PartnerOrder */
    private $partnerOrder;
    /** @var Partner */
    private $partner;
    private $jobDiscountHandler;

    public function __construct(JobDiscountHandler $job_discount_handler)
    {
        $this->jobDiscountHandler = $job_discount_handler;
    }

    /**
     * @param Job $job
     * @return JobDeliveryChargeCalculator
     */
    public function setJob($job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param PartnerOrder $partnerOrder
     * @return JobDeliveryChargeCalculator
     */
    public function setPartnerOrder($partnerOrder)
    {
        $this->partnerOrder = $partnerOrder;
        return $this;
    }

    /**
     * Partner for whom Logistic charges will be calculated
     * @param Partner $partner
     * @return JobDeliveryChargeCalculator
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return Job
     * @throws \Sheba\Dal\Discount\InvalidDiscountType
     */
    public function getCalculatedJob()
    {
        $delivery_charge = new DeliveryCharge();
        $delivery_charge->setCategory($this->job->category)->setLocation($this->partnerOrder->order->location);
        if ($this->partner) {
            $category_partner = CategoryPartner::where([['category_id', $this->job->category_id], ['partner_id', $this->partner->id]])->first();
            $delivery_charge->setCategoryPartnerPivot($category_partner);
        }
        $charge = $delivery_charge->get();
        $this->job->delivery_charge = $delivery_charge->doesUseShebaLogistic() ? 0 : $charge;
        $this->job->logistic_charge = $delivery_charge->doesUseShebaLogistic() ? $charge : 0;
        if ($delivery_charge->doesUseShebaLogistic()) {
            $this->job->needs_logistic = 1;
            $this->job->logistic_parcel_type = $this->job->category->logistic_parcel_type;
            $this->job->logistic_nature = $this->job->category->logistic_nature;
            $this->job->one_way_logistic_init_event = $this->job->category->one_way_logistic_init_event;
        }
        $this->partnerOrder->calculate(1);
        $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($charge)->setOrderAmount($this->partnerOrder->grossAmount);
        $this->jobDiscountHandler->setType(DiscountTypes::DELIVERY)->setCategory($this->job->category)->setCheckingParams($discount_checking_params)->calculate();
        if ($this->jobDiscountHandler->hasDiscount()) {
            $this->jobDiscountHandler->create($this->job);
            $this->job->discount += $this->jobDiscountHandler->getApplicableAmount();
        }
        $this->job->update();
        return $this->job;
    }

}
