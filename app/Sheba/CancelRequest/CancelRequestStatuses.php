<?php namespace Sheba\CancelRequest;

use Sheba\Helpers\ConstGetter;

class CancelRequestStatuses
{
    use ConstGetter;

    const PENDING = "Pending";
    const APPROVED = "Approved";
    const DISAPPROVED = "Disapproved";
}