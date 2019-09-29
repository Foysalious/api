<?php namespace Sheba\Checkout\Partners;

use Sheba\Helpers\ConstGetter;

class PartnerUnavailabilityReasons
{
    use ConstGetter;

    const PREPARATION_TIME = "preparation_time";
    const ON_LEAVE = "on_leave";
    const WORKING_HOUR = "working_hour";
    const RESOURCE_BOOKED = "resource_booked";
}