<?php


namespace Sheba\PaymentLink;

use Sheba\Helpers\ConstGetter;

class PaymentLinkStatus
{
    use ConstGetter;

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const UNREGISTERED = 'unregistered';
    const PROCESSING = 'processing';
    const SUCCESSFUL = 'successful';
    const REJECTED = 'rejected';
}