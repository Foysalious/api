<?php namespace Sheba\Logistics\DTO;

use Carbon\Carbon;
use Sheba\Helpers\BasicGetter;
use Sheba\Logistics\Literals\LogisticOrderKeys;
use Sheba\Logistics\Literals\Statuses;

class Order
{
    use BasicGetter;

    private $id;
    /** @var Carbon */
    private $schedule;
    /** @var Point */
    private $pickUp;
    /** @var Point */
    private $dropOff;
    private $customerProfileId;
    private $parcelType;
    private $successUrl;
    private $pickedUrl;
    private $failureUrl;
    private $collectionUrl;
    private $payUrl;
    private $riderNotFoundUrl;
    /** @var VendorOrder */
    private $vendorOrder;
    private $paidAmount;
    private $isInstant;
    private $collectableAmount;
    private $discount;
    private $isDiscountInPercentage;
    private $rider;
    private $status;

    private $touched = [];

    /**
     * @param int $id
     *
     * @return Order
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param Carbon $schedule
     *
     * @return Order
     */
    public function setSchedule(Carbon $schedule)
    {
        $this->schedule = $schedule;
        $this->touched += $this->getDateTimeArray();
        return $this;
    }

    /**
     * @param Point $pick_up
     *
     * @return Order
     */
    public function setPickUp(Point $pick_up)
    {
        $this->pickUp = $pick_up;
        $this->touched += $this->getPickUpArray();
        return $this;
    }

    /**
     * @param Point $drop_off
     *
     * @return Order
     */
    public function setDropOff($drop_off)
    {
        $this->dropOff = $drop_off;
        $this->touched += $this->getDropOffArray();
        return $this;
    }

    /**
     * @param int $customer_profile_id
     *
     * @return Order
     */
    public function setCustomerProfileId($customer_profile_id)
    {
        $this->customerProfileId = $customer_profile_id;
        $this->touched[LogisticOrderKeys::CUSTOMER_PROFILE_ID] = $this->customerProfileId;
        return $this;
    }

    /**
     * @param  $parcel_type
     *
     * @return Order
     */
    public function setParcelType($parcel_type)
    {
        $this->parcelType = $parcel_type;
        $this->touched[LogisticOrderKeys::PARCEL_TYPE] = $this->parcelType;
        return $this;
    }

    /**
     * @param string $success_url
     *
     * @return Order
     */
    public function setSuccessUrl($success_url)
    {
        $this->successUrl = $success_url;
        $this->touched[LogisticOrderKeys::SUCCESS_URL] = $this->successUrl;
        return $this;
    }

    /**
     * @param string $picked_url
     *
     * @return Order
     */
    public function setPickedUrl($picked_url)
    {
        $this->pickedUrl = $picked_url;
        $this->touched[LogisticOrderKeys::PICKED_URL] = $this->pickedUrl;
        return $this;
    }

    /**
     * @param string $failure_url
     *
     * @return Order
     */
    public function setFailureUrl($failure_url)
    {
        $this->failureUrl = $failure_url;
        $this->touched[LogisticOrderKeys::FAILURE_URL] = $this->failureUrl;
        return $this;
    }

    /**
     * @param string $collection_url
     *
     * @return Order
     */
    public function setCollectionUrl($collection_url)
    {
        $this->collectionUrl = $collection_url;
        $this->touched[LogisticOrderKeys::COLLECTION_URL] = $this->collectionUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getRiderNotFoundUrl()
    {
        return $this->riderNotFoundUrl;
    }

    /**
     * @param string $rider_not_found_url
     * @return Order
     */
    public function setRiderNotFoundUrl($rider_not_found_url)
    {
        $this->riderNotFoundUrl = $rider_not_found_url;
        $this->touched[LogisticOrderKeys::RIDER_NOT_FOUND_URL] = $this->riderNotFoundUrl;
        return $this;
    }

    /**
     * @param VendorOrder $vendor_order
     *
     * @return Order
     */
    public function setVendorOrder(VendorOrder $vendor_order)
    {
        $this->vendorOrder = $vendor_order;
        $this->touched[LogisticOrderKeys::VENDOR_ORDER_DETAIL] = $this->vendorOrder->toJson();
        return $this;
    }

    /**
     * @param float $paid_amount
     *
     * @return Order
     */
    public function setPaidAmount($paid_amount)
    {
        $this->paidAmount = $paid_amount;
        $this->touched[LogisticOrderKeys::PAID_AMOUNT] = $paid_amount;
        return $this;
    }

    /**
     * @param bool $is_instant
     *
     * @return Order
     */
    public function setIsInstant($is_instant)
    {
        $this->isInstant = $is_instant;
        $this->touched[LogisticOrderKeys::IS_INSTANT] = $is_instant;
        return $this;
    }

    /**
     * @param float $collectable_amount
     *
     * @return Order
     */
    public function setCollectableAmount($collectable_amount)
    {
        $this->collectableAmount = $collectable_amount;
        $this->touched[LogisticOrderKeys::COLLECTABLE_AMOUNT] = $collectable_amount;
        return $this;
    }

    /**
     * @param float $discount
     *
     * @return Order
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        $this->touched[LogisticOrderKeys::DISCOUNT] = $discount;
        return $this;
    }

    /**
     * @param array $discount
     *
     * @return Order
     */
    public function setDiscountByArray(array $discount)
    {
        $this->setDiscount($discount['amount'])->setIsDiscountInPercentage($discount['is_percentage']);
        return $this;
    }

    /**
     * @param bool $is_discount_in_percentage
     *
     * @return Order
     */
    public function setIsDiscountInPercentage($is_discount_in_percentage)
    {
        $this->isDiscountInPercentage = $is_discount_in_percentage;
        $this->touched[LogisticOrderKeys::IS_PERCENTAGE] = $is_discount_in_percentage;
        return $this;
    }

    /**
     * @param string $pay_url
     * @return Order
     */
    public function setPayUrl($pay_url)
    {
        $this->payUrl = $pay_url;
        $this->touched[LogisticOrderKeys::PAY_URL] = $pay_url;
        return $this;
    }

    /**
     * @param mixed $rider
     * @return Order
     */
    public function setRider($rider)
    {
        $this->rider = $rider;
        return $this;
    }

    /**
     * @param string $status
     * @return Order
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getReadableStatus()
    {
        return Statuses::getReadable($this->status);
    }

    public function isReschedulable()
    {
        return Statuses::isReschedulable($this->status);
    }

    public function isPickUpDataChangeable()
    {
        return Statuses::isPickUpDataChangeable($this->status);
    }

    public function hasStarted()
    {
        return Statuses::hasStarted($this->status);
    }

    public function getPickUpArray()
    {
        return [
            LogisticOrderKeys::PICKUP_NAME           => $this->pickUp->name,
            LogisticOrderKeys::PICKUP_IMAGE          => $this->pickUp->image,
            LogisticOrderKeys::PICKUP_MOBILE         => $this->pickUp->mobile,
            LogisticOrderKeys::PICKUP_ADDRESS        => $this->pickUp->address,
            LogisticOrderKeys::PICKUP_ADDRESS_GEO    => $this->pickUp->coordinate->toJson()
        ];
    }

    public function getDropOffArray()
    {
        return [
            LogisticOrderKeys::DELIVERY_NAME         => $this->dropOff->name,
            LogisticOrderKeys::DELIVERY_IMAGE        => $this->dropOff->image,
            LogisticOrderKeys::DELIVERY_MOBILE       => $this->dropOff->mobile,
            LogisticOrderKeys::DELIVERY_ADDRESS      => $this->dropOff->address,
            LogisticOrderKeys::DELIVERY_ADDRESS_GEO  => $this->dropOff->coordinate->toJson(),
        ];
    }

    public function getDateTimeArray()
    {
        return [
            LogisticOrderKeys::DATE => $this->schedule->toDateString(),
            LogisticOrderKeys::TIME => $this->schedule->toTimeString()
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            LogisticOrderKeys::CUSTOMER_PROFILE_ID   => $this->customerProfileId,
        ] + $this->getDateTimeArray() + $this->getPickUpArray() + $this->getDropOffArray() + [
            LogisticOrderKeys::PARCEL_TYPE           => $this->parcelType,
            LogisticOrderKeys::SUCCESS_URL           => $this->successUrl,
            LogisticOrderKeys::PICKED_URL            => $this->pickedUrl,
            LogisticOrderKeys::FAILURE_URL           => $this->failureUrl,
            LogisticOrderKeys::COLLECTION_URL        => $this->collectionUrl,
            LogisticOrderKeys::PAY_URL               => $this->payUrl,
            LogisticOrderKeys::RIDER_NOT_FOUND_URL   => $this->riderNotFoundUrl,
            LogisticOrderKeys::VENDOR_ORDER_DETAIL   => $this->vendorOrder->toJson(),
            LogisticOrderKeys::PAID_AMOUNT           => $this->paidAmount,
            LogisticOrderKeys::IS_INSTANT            => $this->isInstant,
            LogisticOrderKeys::COLLECTABLE_AMOUNT    => $this->collectableAmount,
            LogisticOrderKeys::DISCOUNT              => $this->discount,
            LogisticOrderKeys::IS_PERCENTAGE         => $this->isDiscountInPercentage,
        ];
    }

    public function getTouchedArray()
    {
        return $this->touched;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function formatForPartner()
    {
        return [
            'status' => $this->getReadableStatus(),
            'original_status' => $this->status,
            'data' => [
                'rider' => $this->rider,
                'order_id' => $this->id
            ]
        ];
    }

    public function getCollectableUpdateArray()
    {
        return [
            'vendor_collectable_amount' => $this->collectableAmount
        ];
    }
}
