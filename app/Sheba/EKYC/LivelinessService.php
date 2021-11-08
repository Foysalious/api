<?php namespace Sheba\EKYC;

class LivelinessService
{
    /**
     * @var EkycClient
     */
    private $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function getLivelinessConfigurations()
    {
        $url = "liveliness-credentials";
        $configuration = $this->client->get($url);
        return $configuration['data'];
    }
}