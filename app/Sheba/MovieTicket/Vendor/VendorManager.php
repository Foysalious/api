<?php namespace Sheba\MovieTicket\Vendor;


use GuzzleHttp\Client;
use Sheba\MovieTicket\Actions;

class VendorManager
{
    /**
     * @var Vendor $vendor
     */
    private $vendor;
    private $httpClient;

    public function __construct(Client $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param Vendor $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
        return $this;
    }

    public function initVendor()
    {
        $this->vendor->init();
    }

    public function get()
    {
        $response =  $this->httpClient->get($this->vendor->generateURIForAction(Actions::GET_MOVIE_LIST));
        $body = $response->getBody()->getContents();
        return $this->isJson($body) ? :$this->parse($body);
    }

    private function parse ($fileContents) {
        $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
        $fileContents = trim(str_replace('"', "'", $fileContents));
        $fileContents = trim(str_replace('&', "&amp;", $fileContents));
        $simpleXml = simplexml_load_string($fileContents);
        return $simpleXml;
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}