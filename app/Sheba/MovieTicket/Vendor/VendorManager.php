<?php namespace Sheba\MovieTicket\Vendor;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
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

    /**
     * @param $action
     * @param array $params
     * @throws GuzzleException
     */
    public function get($action, $params = [])
    {
        try {
            $response = $this->httpClient->request('GET', $this->vendor->generateURIForAction($action, $params));
            $body = $response->getBody()->getContents();
            return $this->isJson($body) ? $body :$this->parse($body);
        } catch (GuzzleException $e) {
            throw $e;
        }

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