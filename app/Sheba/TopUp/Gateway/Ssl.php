<?php namespace Sheba\TopUp\Gateway;


use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Internal\SslClient;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Ssl implements Gateway
{
    private $ssl;

    public function __construct(SslClient $ssl)
    {
        $this->ssl = $ssl;
    }
    
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        return $this->ssl->recharge($topup_order);
    }

    public function getInitialStatus()
    {
        return config('topup.status.pending')['sheba'];
    }
}