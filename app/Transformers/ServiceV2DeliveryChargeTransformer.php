<?php namespace App\Transformers;

use App\Models\Category;
use App\Models\Location;
use League\Fractal\TransformerAbstract;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;

class ServiceV2DeliveryChargeTransformer extends TransformerAbstract
{
    /** @var DeliveryCharge $deliveryCharge */
    private $deliveryCharge;
    /** @var JobDiscountHandler $jobDiscountHandler */
    private $jobDiscountHandler;
    /** @var Location $location */
    private $location;

    /**
     * ServiceV2Transformer constructor.
     *
     * @param DeliveryCharge $delivery_charge
     * @param JobDiscountHandler $job_discount_handler
     * @param Location $location
     */
    public function __construct(DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler, Location $location)
    {
        $this->deliveryCharge = $delivery_charge;
        $this->jobDiscountHandler = $job_discount_handler;
        $this->location = $location;
    }

    /**
     * @param Category $category
     * @return array
     * @throws InvalidDiscountType
     */
    public function transform(Category $category)
    {
        $original_delivery_charge = $this->deliveryCharge->setCategory($category)->setLocation($this->location)->get();
        $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($original_delivery_charge);
        $this->jobDiscountHandler->setType(DiscountTypes::DELIVERY)->setCategory($category)->setCheckingParams($discount_checking_params)->calculate();
        /** @var Discount $delivery_discount */
        $delivery_discount = $this->jobDiscountHandler->getDiscount();

        return [
            'delivery_charge' => $original_delivery_charge,
            'delivery_discount' => $delivery_discount ? [
                'value' => (double)$delivery_discount->amount,
                'is_percentage' => $delivery_discount->is_percentage,
                'cap' => (double)$delivery_discount->cap,
                'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
            ] : null
        ];
    }
}
