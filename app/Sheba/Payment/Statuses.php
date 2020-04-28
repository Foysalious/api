<?php namespace Sheba\Payment;

use Sheba\Helpers\ConstGetter;

class Statuses
{
    use ConstGetter;

    const INITIATED = 'initiated';
    const INITIATION_FAILED = 'initiation_failed';
    const VALIDATED = 'validated';
    const COMPLETED = 'completed';
    const VALIDATION_FAILED = 'validation_failed';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';
}
