<?php


namespace Sheba\TopUp\Gateway;


use App\Models\TopUpOrder;
use App\Sheba\TopUp\Exception\BdRechargeTopUpStillProcessing;
use App\Sheba\TopUp\Vendor\Internal\BdRechargeClient;
use App\Sheba\TopUp\Vendor\Response\Ipn\BdRecharge\BdRechargeFailResponse;
use App\Sheba\TopUp\Vendor\Response\Ipn\BdRecharge\BdRechargeSuccessResponse;
use Exception;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Vendor\Response\BdRechargeResponse;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyServerError;

class BdRecharge implements Gateway
{
    CONST SHEBA_COMMISSION = 0.0;
    CONST SUCCESS = 1;
    CONST FAILED = 2;
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
        return $response;
    }

    public function getInitialStatus()
    {
        return self::getInitialStatusStatically();
    }

    public static function getInitialStatusStatically()
    {
        return Statuses::ATTEMPTED;
    }

    public function getShebaCommission()
    {
        return self::SHEBA_COMMISSION;
    }

    public function getName()
    {
        return Names::BD_RECHARGE;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return IpnResponse
     * @throws TPProxyServerError | BdRechargeTopUpStillProcessing
     */
    public function enquireIpnResponse(TopUpOrder $topup_order): IpnResponse
    {
        $response = $this->client->enquiry($topup_order);
        /** @var IpnResponse $ipn_response */
        $ipn_response = null;
        $data = property_exists($response, 'data') ? $response->data : $response;
        $status = $data->status;
        if ($status == 'success' ) {
            $ipn_response = app(BdRechargeSuccessResponse::class);
        } else if ($status == 'failed' || $status == 400) {
            $ipn_response = app(BdRechargeFailResponse::class);
        } else if ($status == 'processing') {
            Throw new BdRechargeTopUpStillProcessing($response);
        }
        $ipn_response->setResponse($data);
        return $ipn_response;
    }
}