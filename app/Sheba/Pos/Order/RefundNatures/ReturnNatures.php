<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Helpers\ConstGetter;

class ReturnNatures
{
    use ConstGetter;

    const PARTIAL_RETURN = "partial_return";
    const FULL_RETURN = "full_return";
}