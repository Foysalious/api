<?php


namespace Sheba\Payment\Methods\Nagad;


use Sheba\TPProxy\TPRequest;

class NagadHttpClient
{
    public function call(TPRequest $request)
    {
        $url     = curl_init($request->getUrl());
        $inputs  = $request->getInput();
        $headers = $request->getHeaders();
        curl_setopt($url, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $inputs);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, 0);
        $resultData  = curl_exec($url);
        $ResultArray = json_decode($resultData, true);
        curl_close($url);
        return $ResultArray;
    }

    private function makeHeaders(array $getHeaders)
    {
        $headers = [];
        foreach ($getHeaders as $key => $header) {
            array_push($headers, "$key:$header");
        }
        return $headers;
    }

}
