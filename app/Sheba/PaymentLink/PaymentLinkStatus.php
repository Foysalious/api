<?php


namespace Sheba\PaymentLink;

use Sheba\Helpers\ConstGetter;

class PaymentLinkStatus
{
    use ConstGetter;

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const UNREGISTERED = 'unregistered';
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const VERIFIED = 'verified';
    const REJECTED = 'rejected';
}