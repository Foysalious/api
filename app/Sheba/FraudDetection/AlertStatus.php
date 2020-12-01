<?php namespace App\Sheba\FraudDetection;

use Sheba\Helpers\ConstGetter;

class AlertStatus
{
    use ConstGetter;

    const PENDING = 'pending';
    const ACKNOWLEDGED = 'acknowledged';
    const CLOSED = 'closed';
}
