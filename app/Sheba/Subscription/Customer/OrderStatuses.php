<?php namespace Sheba\Subscription\Customer;

use Sheba\Helpers\ConstGetter;

class OrderStatuses
{
    use ConstGetter;
    const ACCEPTED = 'accepted';
    const COMPLETED = 'completed';
}