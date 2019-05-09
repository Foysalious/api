<?php namespace Sheba\TopUp\Vendor\Internal;

use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TopUp\Vendor\Internal\Pretups\Client as PretupsClient;

trait Pretups
{
    private $pretups;

    public function __construct(PretupsClient $pretups)
    {
        $this->pretups = $pretups;
    }

    /**
     * @param TopUpRequest $top_up_request
     * @return TopUpResponse
     * @throws \Exception
     */
    public function recharge(TopUpRequest $top_up_request): TopUpResponse
    {
        $pretups = $this->pretups->setPin($this->getPin())->setMId($this->getMid())->setUrl($this->getUrl())
            ->setEXTNWCODE($this->getEXTNWCODE())->setAmountMultiplier($this->getAmountMultiplier())
            ->setLanguage1($this->getLanguage1())->setLanguage2($this->getLanguage2())->setSelectors($this->getSelectors());

        if($this->needsProxy()) $pretups->setProxyUrl($this->getVPNServer() . "/v2/proxy/top-up");

        return $pretups->recharge($top_up_request);
    }

    public function getTopUpInitialStatus()
    {
        return config('topup.status.successful')['sheba'];
    }

    private function needsProxy()
    {
        return config('app.url') != $this->getVPNServer();
    }
}