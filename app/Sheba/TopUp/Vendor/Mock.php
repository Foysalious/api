<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Mock extends Vendor
{
    function recharge(TopUpRequest $top_up_request): TopUpResponse
    {
        // TODO: Implement recharge() method.
    }
}