<?php namespace Sheba\Payment\Methods;

use Carbon\Carbon;
use Cache;

use Sheba\Payment\PayChargable;

class Cbl implements PayChargeMethod
{
    private $tunnelHost;
    private $tunnelPort;
    private $merchantId;

    private $acceptUrl;
    private $cancelUrl;
    private $declineUrl;

    private $message;
    private $error = [];

    public function __construct()
    {
        $this->tunnelHost = config('payment.cbl.tunnel_host');
        $this->tunnelPort = config('payment.cbl.tunnel_port');
        $this->merchantId = config('payment.cbl.merchant_id');

        $this->acceptUrl = config('payment.cbl.urls.approve');
        $this->cancelUrl = config('payment.cbl.urls.cancel');
        $this->declineUrl = config('payment.cbl.urls.decline');
    }

    /**
     * @param PayChargable $pay_chargable
     * @return array|null
     * @throws \Exception
     */
    public function init(PayChargable $pay_chargable)
    {
        $response = $this->postQW($this->makeOrderCreateData($pay_chargable));

        $order_id = reset($response->Response->Order->OrderID);
        $session_id = reset($response->Response->Order->SessionID);
        $url = reset($response->Response->Order->URL);


        if (!$order_id || !$session_id) return null;

        $invoice = "SHEBA_CBL_" . $order_id . '_' . $session_id;
        $response->name = 'online';
        $payment_info = [
            'transaction_id' => $invoice,
            'id' => $pay_chargable->id,
            'type' => $pay_chargable->type,
            'pay_chargable' => serialize($pay_chargable),
            'link' => $url . "?ORDERID=" . $order_id . "&SESSIONID=" . $session_id . "",
            'method_info' => $response,
            'order_id' => $order_id,
            'session_id' => $session_id
        ];
        Cache::store('redis')->put("paycharge::$invoice", json_encode($payment_info), Carbon::tomorrow());
        array_forget($payment_info, 'pay_chargable');
        array_forget($payment_info, 'method_info');
        return $payment_info;
    }

    /**
     * @param $payment
     * @return null
     * @throws \Exception
     */
    public function validate($payment)
    {
        $xml = $this->postQW($this->makeOrderInfoData($payment));
        $status = $xml->Response->Order->row->Orderstatus;
        if (!$status) {
            $this->message = 'Validation Failed. Response status is ' . $status;
            return null;
        }
        $res = json_decode(json_encode($xml->Response));
        $res->transaction_id = $payment->transaction_id;
        return $res;
    }

    public function formatTransactionData($method_response)
    {
        return [
            'name' => 'Online',
            'details' => [
                'transaction_id' => $method_response->transaction_id,
                'gateway' => "cbl",
                'details' => $method_response
            ]
        ];
    }

    public function getError(): PayChargeMethodError
    {
        // TODO: Implement getError() method.
    }

    public function __get($name)
    {
        return $this->$name;
    }

    private function makeOrderCreateData(PayChargable $pay_chargable)
    {
        $data = '<?xml version="1.0" encoding="UTF-8"?>';
        $data .= "<TKKPG>";
        $data .= "<Request>";
        $data .= "<Operation>CreateOrder</Operation>";
        $data .= "<Language>EN</Language>";
        $data .= "<Order>";
        $data .= "<OrderType>Purchase</OrderType>";
        $data .= "<Merchant>$this->merchantId</Merchant>";
        $data .= "<Amount>" . $pay_chargable->amount * 100 . "</Amount>";
        $data .= "<Currency>050</Currency>";
        $data .= "<Description>blah blah blah</Description>";
        $data .= "<ApproveURL>" . htmlentities($this->acceptUrl) . "</ApproveURL>";
        $data .= "<CancelURL>" . htmlentities($this->cancelUrl) . "</CancelURL>";
        $data .= "<DeclineURL>" . htmlentities($this->declineUrl) . "</DeclineURL>";
        $data .= "</Order></Request></TKKPG>";
        return $data;
    }

    private function makeOrderInfoData($payment)
    {
        $data = '<?xml version="1.0" encoding="UTF-8"?>';
        $data .= "<TKKPG>";
        $data .= "<Request>";
        $data .= "<Operation>GetOrderInformation</Operation>";
        $data .= "<Language>EN</Language>";
        $data .= "<Order>";
        $data .= "<Merchant>$this->merchantId</Merchant>";
        $data .= "<OrderID>" . $payment->order_id . "</OrderID>";
        $data .= "</Order>";
        $data .= "<SessionID>" . $payment->session_id . "</SessionID>";
        $data .= "<ShowParams>true</ShowParams>";
        $data .= "<ShowOperations>false</ShowOperations>";
        $data .= "<ClassicView>true</ClassicView>";
        $data .= "</Request></TKKPG>";
        return $data;
    }

    /**
     * @param $data
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    private function postQW($data)
    {
        $path = '/Exec';
        $content = '';

        $fp = fsockopen($this->tunnelHost, $this->tunnelPort, $err_no, $err_str, 30);
        if (!$fp) throw new \Exception("$err_str ($err_no)");

        $headers = 'POST ' . $path . " HTTP/1.0\r\n";
        $headers .= 'Host: ' . $this->tunnelHost . "\r\n";
        $headers .= "Content-type: application/x-www-form-urlencoded\r\n";
        $headers .= 'Content-Length: ' . strlen($data) . "\r\n\r\n";

        fwrite($fp, $headers . $data);

        while (!feof($fp)) {
            $inStr = fgets($fp, 1024);
            $content .= $inStr;
        }
        fclose($fp);

        // Cut the HTTP response headers. The string can be commented out if it is necessary to parse the header
        // In this case it is necessary to cut the response
        $content = substr($content, strpos($content, "<TKKPG>"));

        return simplexml_load_string($content);
    }
}