<?php namespace Sheba\Reward;

use Sheba\Helpers\ConstGetter;

class RewardableTypes
{
    use ConstGetter;

    const CUSTOMER = 'customer';
    const PARTNER = 'partner';
    const RESOURCE = 'resource';
}