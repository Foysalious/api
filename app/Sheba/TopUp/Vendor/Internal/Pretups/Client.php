<?php namespace Sheba\TopUp\Vendor\Internal\Pretups;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\TopUp\Vendor\Response\PretupsResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Carbon\Carbon;

class Client
{
    /** @var HttpClient */
    private $httpClient;

    private $pin;
    private $mId;
    private $url;
    private $vpnUrl;
    private $EXTNWCODE;
    private $language1;
    private $language2;
    private $selectors;
    private $amountMultiplier;

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
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
        $this->url = $url;
        return $this;
    }

    public function setVpnUrl($url)
    {
        $this->vpnUrl = $url;
        return $this;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     * @throws GuzzleException
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $vpn_response = $this->call($this->makeInputString($topup_order));
        $rax_response = new PretupsResponse();
        if ($vpn_response) $rax_response->setResponse($vpn_response);
        dd($vpn_response, $rax_response);
    }

    private function makeInputString(TopUpOrder $topup_order)
    {
        $input = '<?xml version="1.0"?><COMMAND>';
        $input .= "<TYPE>" . $this->getType($topup_order->payee_mobile_type) . "</TYPE>";
        $input .= "<DATE>" . Carbon::now()->toDateTimeString() . "</DATE>";
        $input .= "<EXTNWCODE>$this->EXTNWCODE</EXTNWCODE>";
        $input .= "<MSISDN>$this->mId</MSISDN>";
        $input .= "<PIN>$this->pin</PIN>";
        $input .= '<LOGINID></LOGINID>';
        $input .= '<PASSWORD></PASSWORD>';
        $input .= '<EXTCODE></EXTCODE>';
        $input .= '<EXTREFNUM></EXTREFNUM>';
        $input .= "<MSISDN2>" . $topup_order->getOriginalMobile() . "</MSISDN2>";
        $input .= "<AMOUNT>" . ($topup_order->amount * $this->amountMultiplier) . "</AMOUNT>";
        $input .= "<LANGUAGE1>" . $this->language1 . "</LANGUAGE1>";
        $input .= "<LANGUAGE2>" . $this->language2 . "</LANGUAGE2>";
        $input .= "<SELECTOR>" . $this->selectors[$topup_order->payee_mobile_type] . "</SELECTOR>";
        $input .= '</COMMAND>';
        return $input;
    }

    private function getType($type)
    {
        return $type == 'prepaid' ? 'EXRCTRFREQ' : 'EXPPBREQ';
    }

    /**
     * @param $input
     * @return array
     * @throws GuzzleException
     * @throws Exception
     */
    private function call($input)
    {
        $result = $this->httpClient->request('POST', $this->vpnUrl, [
            'form_params' => [
                'url' => $this->url,
                'input' => $input
            ],
            'timeout' => 60,
            'read_timeout' => 60,
            'connect_timeout' => 60
        ]);
        $vpn_response = $result->getBody()->getContents();
        dd($this->url);
        if (!$vpn_response) throw new Exception("Vpn server not working.");
        $vpn_response = json_decode($vpn_response);
        if ($vpn_response->code != 200) throw new Exception("Vpn server error: ". $vpn_response->message);
        if (!$vpn_response->data) return null;
        return $vpn_response->data;
    }
}
