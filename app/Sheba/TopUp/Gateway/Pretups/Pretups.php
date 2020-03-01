<?php namespace Sheba\TopUp\Gateway\Pretups;


use App\Models\TopUpOrder;
use Sheba\TopUp\Gateway\Gateway;
use Sheba\TopUp\Vendor\Internal\Pretups\Client as PretupsClient;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

abstract class Pretups implements Gateway
{
    private $pretups;

    public function __construct(PretupsClient $pretups)
    {
        $this->pretups = $pretups;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws \Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $pretups = $this->pretups->setPin($this->getPin())->setMId($this->getMid())->setUrl($this->getUrl())
            ->setEXTNWCODE($this->getEXTNWCODE())->setAmountMultiplier($this->getAmountMultiplier())
            ->setLanguage1($this->getLanguage1())->setLanguage2($this->getLanguage2())->setSelectors($this->getSelectors());

        if ($this->needsProxy()) $pretups->setProxyUrl($this->getVPNServer() . "/v2/proxy/top-up");

        return $pretups->recharge($topup_order);
    }

    private function needsProxy()
    {
        return config('app.url') != $this->getVPNServer();
    }

    public function getInitialStatus()
    {
        return config('topup.status.successful')['sheba'];
    }

    abstract protected function getPin();

    abstract protected function getMid();

    abstract protected function getEXTNWCODE();

    abstract protected function getAmountMultiplier();

    abstract protected function getLanguage1();

    abstract protected function getLanguage2();

    abstract protected function getSelectors();

    abstract protected function getVPNServer();

    abstract protected function getUrl();

}