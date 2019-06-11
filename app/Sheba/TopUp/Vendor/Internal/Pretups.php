<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
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
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws \Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $pretups = $this->pretups->setPin($this->getPin())->setMId($this->getMid())->setUrl($this->getUrl())
            ->setEXTNWCODE($this->getEXTNWCODE())->setAmountMultiplier($this->getAmountMultiplier())
            ->setLanguage1($this->getLanguage1())->setLanguage2($this->getLanguage2())->setSelectors($this->getSelectors());

        if($this->needsProxy()) $pretups->setProxyUrl($this->getVPNServer() . "/v2/proxy/top-up");

        return $pretups->recharge($topup_order);
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