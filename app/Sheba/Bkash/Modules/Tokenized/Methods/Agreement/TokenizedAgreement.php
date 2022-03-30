<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement;


use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses\CreateResponse;
use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses\ExecuteResponse;
use Sheba\Bkash\Modules\Tokenized\TokenizedModule;
use Sheba\Settings\Payment\Responses\InitResponse;
use Sheba\Settings\Payment\Responses\ValidateResponse;

class TokenizedAgreement extends TokenizedModule
{

    /**
     * @param $payer_reference
     * @param $callback_url
     * @return InitResponse
     * @throws \Exception
     */
    public function create($payer_reference, $callback_url): InitResponse
    {
        $createagreementbody = array(
            'payerReference' => (string)$payer_reference,
            'callbackURL' => $callback_url,
            'mode' => '0000',
        );
        $url = curl_init($this->bkashAuth->url . '/checkout/create');
        $createagreementbodyx = json_encode($createagreementbody);
        curl_setopt($url, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $createagreementbodyx);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $result_data = curl_exec($url);
        $obj = json_decode($result_data);
        if (curl_errno($url) > 0) throw new \Exception('Something went wrong');
        curl_close($url);
        $create_response = new CreateResponse($obj);
        $init_response = new InitResponse();
        if (!$create_response->isSuccess()) throw new \Exception("Something went wrong");
        $init_response->setRedirectUrl($create_response->getRedirectUrl())->setTransactionId($create_response->getTransactionId());
        return $init_response;
    }

    public function execute($payment_id): ValidateResponse
    {
        $requestbody = array('paymentID' => $payment_id);
        $url = curl_init($this->bkashAuth->url . '/checkout/execute');
        $requestbodyJson = json_encode($requestbody);
        curl_setopt($url, CURLOPT_HTTPHEADER,  $this->getHeader());
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdatax = curl_exec($url);
        $obj = json_decode($resultdatax);
        curl_close($url);
        $execute_response = new ExecuteResponse($obj);
        $validate_response = new ValidateResponse();
        if (!$execute_response->isSuccess()) throw new \Exception("Something went wrong");
        $validate_response->setAgreementId($execute_response->getAgreementID());
        return $validate_response;
    }

    /**
     * @return array
     */
    private function getHeader()
    {
        $header = array(
            'Content-Type:application/json',
            'authorization:' . $this->getToken(),
            'x-app-key:' . $this->bkashAuth->appKey);
        return $header;
    }
}