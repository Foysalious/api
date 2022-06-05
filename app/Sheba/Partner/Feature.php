<?php namespace Sheba\Partner;

use Sheba\Helpers\ConstGetter;

class Feature
{
    use ConstGetter;

    const TOPUP = 'topup';
    const SMS = 'sms';
    const DELIVERY = 'delivery';
}