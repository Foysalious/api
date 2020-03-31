<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use GuzzleHttp\Exception\GuzzleException;
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
     * @throws GuzzleException
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $pretups = $this->pretups->setPin($this->getPin())->setMId($this->getMid())->setUrl($this->getUrl())
            ->setEXTNWCODE($this->getEXTNWCODE())->setAmountMultiplier($this->getAmountMultiplier())
            ->setLanguage1($this->getLanguage1())->setLanguage2($this->getLanguage2())
            ->setSelectors($this->getSelectors())->setVpnUrl($this->getVPNUrl());

        return $pretups->recharge($topup_order);
    }

    public function getTopUpInitialStatus()
    {
        return config('topup.status.successful')['sheba'];
    }
}
