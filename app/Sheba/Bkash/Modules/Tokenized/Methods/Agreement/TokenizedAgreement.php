<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement;


use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses\CreateResponse;
use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses\ExecuteResponse;
use Sheba\Bkash\Modules\Tokenized\TokenizedModule;

class TokenizedAgreement extends TokenizedModule
{

    /**
     * @param $payer_reference
     * @param $callback_url
     * @return CreateResponse
     */
    public function create($payer_reference, $callback_url)
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
        if (array_key_exists('errorCode', $obj)) throw new \InvalidArgumentException('Bkash create API error.');
        curl_close($url);
        return (new CreateResponse())->setResponse($obj);
    }

    public function execute($payment_id)
    {
        $post_fields = json_encode(['paymentID' => $payment_id]);
        $curl = curl_init($this->bkashAuth->url . '/checkout/agreement/execute');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($curl);
        if (curl_errno($curl) > 0) throw new \InvalidArgumentException('API error.');
        curl_close($curl);
        return (new ExecuteResponse())->setResponse(json_decode($result_data));
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