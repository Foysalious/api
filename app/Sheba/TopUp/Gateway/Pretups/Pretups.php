<?php namespace Sheba\TopUp\Gateway\Pretups;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Vendor\Internal\Pretups\Client as PretupsClient;
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
        $pretups = $this->pretups->setPin($this->getPin())->setMId($this->getMid())->setUrl($this->getUrl())
            ->setEXTNWCODE($this->getEXTNWCODE())->setAmountMultiplier($this->getAmountMultiplier())
            ->setLanguage1($this->getLanguage1())->setLanguage2($this->getLanguage2())
            ->setSelectors($this->getSelectors())->setVpnUrl($this->getVPNUrl());

        return $pretups->recharge($topup_order);
    }

    public function getInitialStatus()
    {
        return self::getInitialStatusStatically();
    }

    public static function getInitialStatusStatically()
    {
        return config('topup.status.successful.sheba');
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
}
