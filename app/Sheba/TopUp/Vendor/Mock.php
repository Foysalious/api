<?php namespace Sheba\TopUp\Vendor;

use App\Models\TopUpOrder;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\GenericGatewayErrorResponse;
use Sheba\TopUp\Vendor\Response\MockResponse;
use Sheba\TopUp\Vendor\Response\TopUpGatewayTimeoutResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Mock extends Vendor
{
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $this->resolveGateway($topup_order);

        if ($topup_order->payee_mobile == "+8801700999999") return $this->handleGatewayTimeout();

        $is_success = $topup_order->payee_mobile != "+8801700888888";

        return (new MockResponse())->setResponse(json_decode(json_encode([
            'TXNSTATUS' => $is_success ? 200 : 500,
            'TXNID' => 123456,
            'MESSAGE' => "Mocking"
        ])));
    }
}
