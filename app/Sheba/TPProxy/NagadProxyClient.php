<?php namespace Sheba\TPProxy;

use GuzzleHttp\Client as HttpClient;

class NagadProxyClient extends TPProxyClient
{
    public function __construct(HttpClient $client)
    {
        parent::__construct($client);
        $this->proxyUrl = config('sheba.nagad_proxy_url');
    }
}
