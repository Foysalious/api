<?php namespace Sheba\Transport\Bus\Order;

use Sheba\Helpers\ConstGetter;

class Status
{
    use ConstGetter;

    const CONFIRMED = "confirmed";
    const INITIATED = "initiated";
    const FAILED = "failed";
}