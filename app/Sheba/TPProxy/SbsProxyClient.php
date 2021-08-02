<?php namespace Sheba\TPProxy;

use GuzzleHttp\Client as HttpClient;

class SbsProxyClient extends TPProxyClient
{
    public function __construct(HttpClient $client)
    {
        parent::__construct($client);
        $this->proxyUrl = config('sheba.sbs_proxy_url');
    }
}
