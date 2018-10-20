<?php

namespace Sheba\Payment\Adapters\PayCharged;


use Sheba\Payment\PayCharged;

interface PayChargedAdapter
{
    public function getPayCharged(): PayCharged;
}