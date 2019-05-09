<?php namespace Sheba\TopUp\Vendor\Internal\Pretups;

use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\PretupsResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Carbon\Carbon;

class Client
{
    /** @var Caller */
    private $caller;

    private $pin;
    private $mId;
    private $EXTNWCODE;
    private $language1;
    private $language2;
    private $selectors;
    private $amountMultiplier;

    public function __construct(DirectCaller $caller)
    {
        $this->caller = $caller;
    }

    public function setPin($pin)
    {
        $this->pin = $pin;
        return $this;
    }

    public function setMId($mid)
    {
        $this->mId = $mid;
        return $this;
    }

    public function setEXTNWCODE($code)
    {
        $this->EXTNWCODE = $code;
        return $this;
    }

    public function setLanguage1($l1)
    {
        $this->language1 = $l1;
        return $this;
    }

    public function setLanguage2($l1)
    {
        $this->language2 = $l1;
        return $this;
    }

    public function setAmountMultiplier($multiplier)
    {
        $this->amountMultiplier = $multiplier;
        return $this;
    }

    public function setSelectors(array $selectors)
    {
        $this->selectors = $selectors;
        return $this;
    }

    public function setUrl($url)
    {
        $this->caller->setUrl($url);
        return $this;
    }

    public function setProxyUrl($url)
    {
        $this->caller = $this->caller->switchToProxy();
        $this->caller->setProxyUrl($url);
        return $this;
    }

    /**
     * @param TopUpRequest $top_up_request
     * @return TopUpResponse
     * @throws \Exception
     */
    public function recharge(TopUpRequest $top_up_request): TopUpResponse
    {
        $this->caller->setInput($this->makeInputString($top_up_request));
        $response = $this->caller->call();
        $rax_response = new PretupsResponse();
        $rax_response->setResponse($response);
        return $rax_response;
    }

    private function makeInputString(TopUpRequest $top_up_request)
    {
        $input = '<?xml version="1.0"?><COMMAND>';
        $input .= "<TYPE>" . $this->getType($top_up_request->getType()) . "</TYPE>";
        $input .= "<DATE>" . Carbon::now()->toDateTimeString() . "</DATE>";
        $input .= "<EXTNWCODE>$this->EXTNWCODE</EXTNWCODE>";
        $input .= "<MSISDN>$this->mId</MSISDN>";
        $input .= "<PIN>$this->pin</PIN>";
        $input .= '<LOGINID></LOGINID>';
        $input .= '<PASSWORD></PASSWORD>';
        $input .= '<EXTCODE></EXTCODE>';
        $input .= '<EXTREFNUM></EXTREFNUM>';
        $input .= "<MSISDN2>" . $top_up_request->getOriginalMobile() . "</MSISDN2>";
        $input .= "<AMOUNT>" . ($top_up_request->getAmount() * $this->amountMultiplier) . "</AMOUNT>";
        $input .= "<LANGUAGE1>" . $this->language1 . "</LANGUAGE1>";
        $input .= "<LANGUAGE2>" . $this->language2 . "</LANGUAGE2>";
        $input .= "<SELECTOR>" . $this->selectors[$top_up_request->getType()] . "</SELECTOR>";
        $input .= '</COMMAND>';
        return $input;
    }

    private function getType($type)
    {
        return $type == 'prepaid' ? 'EXRCTRFREQ' : 'EXPPBREQ';
    }
}