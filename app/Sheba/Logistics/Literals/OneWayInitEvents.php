<?php namespace Sheba\Logistics\Literals;

use Sheba\Helpers\ConstGetter;

class OneWayInitEvents
{
    use ConstGetter;

    const ORDER_ACCEPT = "order_accept";
    const READY_TO_PICK = "ready_to_pick";
}