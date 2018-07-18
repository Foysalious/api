<?php

namespace Sheba\TopUp;


use GuzzleHttp\Psr7\Request;

class Robi implements Operator
{

    public function recharge($to, $from)
    {
        $request = new Request(
            'POST',
            $uri,
            ['Content-Type' => 'text/xml; charset=UTF8'],
            $xml
        );
    }
}