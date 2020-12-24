<?php namespace Sheba\Business\ProcurementPaymentRequest;

use Sheba\Helpers\ConstGetter;

class Status
{
    use ConstGetter;

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const ACKNOWLEDGED = 'acknowledged';
    const REJECTED = 'rejected';
    const PAID = 'paid';
}
