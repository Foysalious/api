<?php namespace Sheba\TopUp\Vendor;

use App\Models\TopUpOrder;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\MockResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Mock extends Vendor
{
    function recharge(TopUpOrder $topup_order): TopUpResponse
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