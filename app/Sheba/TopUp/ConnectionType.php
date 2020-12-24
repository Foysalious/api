<?php namespace Sheba\TopUp;

use Sheba\Helpers\ConstGetter;

class ConnectionType
{
    use ConstGetter;

    const PREPAID = "prepaid";
    const POSTPAID = "postpaid";
}