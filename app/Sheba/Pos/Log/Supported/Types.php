<?php namespace Sheba\Pos\Log\Supported;

use Sheba\AppSettings\HomePageSetting\Supported\Constants;
use Sheba\Pos\Log\Exceptions\UnsupportedType;

class Types extends Constants
{
    const PARTIAL_RETURN = "partial_return";
    const FULL_RETURN = "full_return";
    const EXCHANGE = "exchange";
    const ITEM_QUANTITY_INCREASE = "item_quantity_increase";

    /**
     * @param $type
     * @throws UnsupportedType
     */
    protected static function throwException($type)
    {
        throw new UnsupportedType($type);
    }
}