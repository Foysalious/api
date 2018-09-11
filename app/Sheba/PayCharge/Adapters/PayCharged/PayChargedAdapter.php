<?php

namespace Sheba\PayCharge\Adapters\PayCharged;


use Sheba\PayCharge\PayCharged;

interface PayChargedAdapter
{
    public function getPayCharged(): PayCharged;
}