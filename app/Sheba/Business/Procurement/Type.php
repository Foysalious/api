<?php namespace Sheba\Business\Procurement;

use Sheba\Helpers\ConstGetter;

class Type
{
    use ConstGetter;

    const BASIC = 'basic';
    const ADVANCED = 'advanced';
    const PRODUCT = 'product';
    const SERVICE = 'service';
}
