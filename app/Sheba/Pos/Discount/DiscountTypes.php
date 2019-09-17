<?php namespace Sheba\Pos\Discount;

use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Helpers\ConstGetter;

class DiscountTypes
{
    use ConstGetter;

    const ORDER    = 'order';
    const SERVICE  = 'service';
    const VOUCHER  = 'voucher';

    /**
     * @param $type
     * @throws InvalidDiscountType
     */
    public static function checkIfValid($type)
    {
        if (!in_array($type, self::get())) throw new InvalidDiscountType($type);
    }
}