<?php namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\MockResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Mock extends Vendor
{
    function recharge(TopUpRequest $top_up_request): TopUpResponse
    {
        return (new MockResponse())->setResponse(json_decode(json_encode([
            'TXNSTATUS' => 200,
            'TXNID' => 123456,
            'MESSAGE' => "Mocking"
        ])));
    }

    function getTopUpInitialStatus()
    {
        return "Successful";
    }
}