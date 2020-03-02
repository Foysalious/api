<?php namespace Sheba\TopUp\Vendor\Internal\Pretups;

use Sheba\Logs\ErrorLog;

class DirectCaller extends Caller
{
    public function call()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml', 'Connection: close']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=$this->input");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $data = curl_exec($ch);
        $err = curl_error($ch);
        if ($err) {
            (new ErrorLog())->setException(new \Exception($err))->send();
            return null;
        }
        curl_close($ch);
        return json_decode(json_encode(simplexml_load_string($data)));
    }
}
