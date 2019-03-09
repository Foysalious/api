<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement;


use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses\CreateResponse;
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
        $create_pay_body = json_encode(array(
            'payerReference' => $payer_reference,
            'callbackURL' => $callback_url,
        ));
        $curl = curl_init($this->getBkashAuth()->url . '/checkout/agreement/create');
        $header = $this->getHeader();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $create_pay_body);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($curl);
        if (curl_errno($curl) > 0) throw new \InvalidArgumentException('Bkash create API error.');
        curl_close($curl);
        return (new CreateResponse())->setResponse(json_decode($result_data));
    }

    public function execute(array $data)
    {
        // TODO: Implement execute() method.
    }

    public function status()
    {
        // TODO: Implement status() method.
    }

    /**
     * @return array
     */
    private function getHeader()
    {
        $header = array(
            'Content-Type:application/json',
            'authorization:' . $this->getToken(),
            'x-app-key:' . $this->getBkashAuth()->appKey);
        return $header;
    }
}