<?php namespace Sheba\Logistics\LogisticsNatures;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\Partner;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Sheba\Logistics\Repository\ParcelRepository;
use Sheba\Logistics\DTO\Point;

abstract class LogisticNature
{
    /** @var Job $job */
    protected $job;
    /** @var PartnerOrder $partnerOrder */
    protected $partnerOrder;
    /** @var Customer */
    protected $customer;
    /** @var Partner */
    protected $partner;
    /** @var CustomerDeliveryAddress */
    protected $customerDeliveryAddress;
    /** @var ParcelRepository $parcelRepo */
    protected $parcelRepo;

    protected $deliveryCharge;

    const PICK_UP_PREPARATION_TIME = 30;

    public function __construct(ParcelRepository $parcel_repo)
    {
        $this->parcelRepo = $parcel_repo;
    }

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job                      = $job;
        $this->partnerOrder             = $job->partnerOrder;
        $order                          = $this->partnerOrder->order;
        $this->partner                  = $this->partnerOrder->partner;
        $this->customer                 = $order->customer->profile;
        $this->customerDeliveryAddress  = $order->deliveryAddress;
        $this->deliveryCharge           = $this->getDeliveryCharge();
        return $this;
    }

    protected function getDeliveryCharge()
    {
        $parcel_details  = $this->parcelRepo->findBySlug($this->job->category->logistic_parcel_type);
        return $parcel_details['price'];
    }

    /**
     * @return Point
     */
    public function getPickUp()
    {
        return $this->getPartnerPoint();
    }
    
    /**
     * @return Point
     */
    public function getDropOff()
    {
        return $this->getCustomerPoint();
    }
    
    /**
     * @return Point
     */
    protected function getPartnerPoint()
    {
        return (new Point())->setName($this->partner->name)
                ->setAddress($this->partner->address)
                ->setImage($this->partner->logo)
                ->setMobile($this->partner->mobile)
                ->setCoordinate($this->partner->getCoordinate());
    }
    
    /**
     * @return Point
     */
    protected function getCustomerPoint()
    {
        return (new Point())->setName($this->customer->name)
                ->setAddress($this->customerDeliveryAddress->address)
                ->setImage($this->customer->pro_pic)
                ->setMobile($this->customer->mobile)
                ->setCoordinate($this->customerDeliveryAddress->getCoordinate());
    }

    /**
     * @return Carbon
     */
    public function getPickupTime()
    {
        return Carbon::parse($this->job->schedule_date . ' ' . $this->job->preferred_time_start)
            ->subMinutes(self::PICK_UP_PREPARATION_TIME);
    }

    public function isInstant()
    {
        return false;
    }

    public function getCollectableAmount()
    {
        $this->partnerOrder = ($this->partnerOrder->isCalculated) ? $this->partnerOrder : $this->partnerOrder->calculate(true);

        return $this->partnerOrder->due > 0 ? $this->partnerOrder->due : 0;
    }

    public function getDiscount()
    {
        if ($this->job->isFlatPercentageDiscount()) return ['amount' => $this->job->discount_percentage, 'is_percentage' => true];

        $amount = ($this->job->originalDiscount > $this->job->totalPrice) ? $this->job->originalDiscount - $this->job->totalPrice : 0;
        return ['amount' => $amount, 'is_percentage' => false];
    }

    public function getPaidAmount()
    {
        if (!$this->partnerOrder->isOverPaid()) return 0.00;

        return ($this->partnerOrder->overPaid > $this->deliveryCharge) ? $this->deliveryCharge : $this->partnerOrder->overPaid;
    }
}