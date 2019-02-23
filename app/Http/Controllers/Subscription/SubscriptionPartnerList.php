<?php

namespace App\Http\Controllers\Subscription;


use App\Sheba\Checkout\PartnerList;

class SubscriptionPartnerList extends PartnerList
{
    private $subscription_dates;
    private $subscription_time;
    private $subscription_type;

    public function __construct()
    {
        parent::__construct();
    }

    public function setSubscriptionDates(array $dates)
    {
        $this->subscription_dates = $dates;
        return;
    }

    public function setSubscriptionTime($time)
    {
        $this->subscription_time = $time;
        return;
    }

    public function setSubscriptionType($type)
    {
        $this->subscription_type = $type;
        return;
    }
}