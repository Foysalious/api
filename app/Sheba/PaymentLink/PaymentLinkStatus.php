<?php


namespace Sheba\PaymentLink;

use Sheba\Helpers\ConstGetter;

class PaymentLinkStatus
{
    use ConstGetter;

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
}