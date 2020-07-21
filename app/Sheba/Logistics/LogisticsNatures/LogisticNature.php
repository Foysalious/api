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
        $this->job = $job;
        $this->partnerOrder = $job->partnerOrder->isCalculated ? $job->partnerOrder : $job->partnerOrder->calculate(true);
        $order = $this->partnerOrder->order;
        $this->partner = $this->partnerOrder->partner;
        $this->customer = $order->customer->profile;
        $this->customerDeliveryAddress = $order->deliveryAddress;
        $this->deliveryCharge = $job->category->getShebaLogisticsPrice();
        return $this;
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
        return $this->createPartnerPoint($this->partner);
    }

    /**
     * @param Partner $partner
     * @return Point
     */
    public function createPartnerPoint(Partner $partner)
    {
        return (new Point())->setName($partner->name)
            ->setAddress($partner->address)
            ->setImage($partner->logo)
            ->setMobile($partner->getContactNumber())
            ->setCoordinate($partner->getCoordinate());
    }

    /**
     * @return Point
     */
    protected function getCustomerPoint()
    {
        return (new Point())->setName($this->customerDeliveryAddress->name ?? $this->partnerOrder->order->delivery_name)
            ->setAddress($this->customerDeliveryAddress->address)
            ->setImage($this->customer->pro_pic)
            ->setMobile($this->customerDeliveryAddress->mobile ?: $this->customer->mobile)
            ->setCoordinate($this->customerDeliveryAddress->getCoordinate());
    }

    /**
     * @return Carbon
     */
    public function getPickupTime()
    {
        return $this->getPickupTimeFromDateTime($this->job->schedule_date, $this->job->preferred_time_start);
    }

    /**
     * @param $date
     * @param $time
     * @return Carbon
     */
    public function getPickupTimeFromDateTime($date, $time)
    {
        return Carbon::parse($date . ' ' . $time)->subMinutes(self::PICK_UP_PREPARATION_TIME);
    }

    public function isInstant()
    {
        return false;
    }

    public function getCollectableAmount()
    {
        return $this->partnerOrder->due > 0 ? $this->partnerOrder->due : 0;
    }

    public function getDiscount()
    {
        return $this->job->isFlatPercentageDiscount() ?
            ['amount' => $this->job->discount_percentage, 'is_percentage' => true] :
            ['amount' => $this->job->getExtraDiscount(), 'is_percentage' => false];
    }

    public function getPaidAmount()
    {
        if (!$this->partnerOrder->isOverPaid()) return 0.00;

        return ($this->partnerOrder->overPaid > $this->deliveryCharge) ? $this->deliveryCharge : $this->partnerOrder->overPaid;
    }
}
