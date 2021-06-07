<?php namespace Sheba\TopUp\Gateway\Pretups;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Vendor\Internal\Pretups\Client as PretupsClient;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

abstract class Pretups
{
    private $pretups;

    public function __construct(PretupsClient $pretups)
    {
        $this->pretups = $pretups;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     * @throws GatewayTimeout
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $pretups = $this->makePretups()
            ->setAmountMultiplier($this->getAmountMultiplier())
            ->setLanguage2($this->getLanguage2())
            ->setSelectors($this->getSelectors());

        return $pretups->recharge($topup_order);
    }

    /**
     * @param TopUpOrder $topup_order
     * @return IpnResponse
     * @throws GatewayTimeout
     */
    public function enquireIpnResponse(TopUpOrder $topup_order): IpnResponse
    {
        return $this->makePretups()->checkStatus($topup_order);
    }

    abstract protected function getPin();

    abstract protected function getMid();

    abstract protected function getEXTNWCODE();

    abstract protected function getAmountMultiplier();

    abstract protected function getLanguage1();

    abstract protected function getLanguage2();

    abstract protected function getSelectors();

    abstract protected function getVPNUrl();

    abstract protected function getUrl();

    private function makePretups()
    {
        return $this->pretups
            ->setPin($this->getPin())
            ->setMId($this->getMid())
            ->setUrl($this->getUrl())
            ->setEXTNWCODE($this->getEXTNWCODE())
            ->setLanguage1($this->getLanguage1())
            ->setVpnUrl($this->getVPNUrl());
    }
}
