<?php namespace Sheba\Logistics\Literals;

use Sheba\Helpers\ConstGetter;

class OrderKeys
{
    use ConstGetter;

    const FIRST = "first_logistic_order_id";
    const LAST = "last_logistic_order_id";
}