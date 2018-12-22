<?php namespace Sheba\Payment\Methods\Cbl;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use Cache;
use Sheba\Payment\Methods\Cbl\Response\InitResponse;
use Sheba\Payment\Methods\Cbl\Response\ValidateResponse;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\PayChargable;
use Sheba\RequestIdentification;
use DB;

class Cbl extends PaymentMethod
{
    private $tunnelHost;
    private $tunnelPort;
    private $merchantId;

    private $acceptUrl;
    private $cancelUrl;
    private $declineUrl;

    private $message;
    CONST NAME = 'cbl';

    public function __construct()
    {
        parent::__construct();
        $this->tunnelHost = config('payment.cbl.tunnel_host');
        $this->tunnelPort = config('payment.cbl.tunnel_port');
        $this->merchantId = config('payment.cbl.merchant_id');

        $this->acceptUrl = config('payment.cbl.urls.approve');
        $this->cancelUrl = config('payment.cbl.urls.cancel');
        $this->declineUrl = config('payment.cbl.urls.decline');
    }

    public function init(Payable $payable): Payment
    {
        $payment = new Payment();
        $user = $payable->user;
        $invoice = "SHEBA_CBL_";
        DB::transaction(function () use ($payment, $payable, $invoice, $user) {
            $payment->payable_id = $payable->id;
            $payment->transaction_id = $invoice;
            $payment->status = 'initiated';
            $payment->valid_till = Carbon::tomorrow();
            $this->setModifier($user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();
            $payment_details = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method = self::NAME;
            $payment_details->amount = $payable->amount;
            $payment_details->save();
        });
        $response = $this->postQW($this->makeOrderCreateData($payable));
        $init_response = new InitResponse();
        $init_response->setResponse($response);
        if ($init_response->hasSuccess()) {
            $success = $init_response->getSuccess();
            $payment->transaction_details = json_encode($success->details);
            $payment->transaction_id = "SHEBA_CBL_" . $invoice . $success->id;
            $payment->redirect_url = $success->redirect_url;
        } else {
            $error = $init_response->getError();
            $this->paymentRepository->setPayment($payment);
            $this->paymentRepository->changeStatus(['to' => 'initiation_failed', 'from' => $payment->status,
                'transaction_details' => json_encode($error->details)]);
            $payment->status = 'initiation_failed';
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }


    public function validate(Payment $payment)
    {
        $xml = $this->postQW($this->makeOrderInfoData($payment));
        $validation_response = new ValidateResponse();
        $validation_response->setResponse($xml);
        $validation_response->setPayment($payment);
        $status = $xml->Response->Order->row->Orderstatus;
        dd($validation_response->hasSuccess());
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

    private function makeOrderCreateData(Payable $payable)
    {
        $data = '<?xml version="1.0" encoding="UTF-8"?>';
        $data .= "<TKKPG>";
        $data .= "<Request>";
        $data .= "<Operation>CreateOrder</Operation>";
        $data .= "<Language>EN</Language>";
        $data .= "<Order>";
        $data .= "<OrderType>Purchase</OrderType>";
        $data .= "<Merchant>$this->merchantId</Merchant>";
        $data .= "<Amount>" . $payable->amount . "</Amount>";
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