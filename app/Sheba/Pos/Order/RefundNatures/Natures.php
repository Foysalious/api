<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Helpers\ConstGetter;

class Natures
{
    use ConstGetter;

    const RETURNED = "returned";
    const EXCHANGED = "exchanged";
}