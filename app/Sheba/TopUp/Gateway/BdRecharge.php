<?php


namespace Sheba\TopUp\Gateway;


use App\Models\TopUpOrder;
use App\Sheba\TopUp\Vendor\Internal\BdRechargeClient;
use Exception;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Vendor\Response\BdRechargeResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class BdRecharge implements Gateway
{
    CONST SHEBA_COMMISSION = 0.0;
    private $client;

    public function __construct(BdRechargeClient $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $response = new BdRechargeResponse();
        $response->setResponse($this->client->recharge($topup_order));
        dd($response);
    }

    public function getInitialStatus()
    {
        return self::getInitialStatusStatically();
    }

    public static function getInitialStatusStatically()
    {
        return Statuses::PENDING;
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName()
    {
        return Names::BD_RECHARGE;
    }
}