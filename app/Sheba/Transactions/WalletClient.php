<?php namespace Sheba\Transactions;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use Throwable;

class WalletClient
{
    /** @var Client $client */
    private $client;
    private $baseWalletUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseWalletUrl = config('wallet.url') . '/api/';
    }

    /**
     * @param array $data
     * @return mixed|stdClass
     * @throws GuzzleException
     */
    public function registerTransaction(array $data)
    {
        $uri = $this->baseWalletUrl . 'transaction/register';
        try {
            $params = ['form_params' => $data];
            $response = $this->client->request('POST', $uri, $params)->getBody()->getContents();
            return json_decode($response);
        } catch (Throwable $exception) {
            $exception = (string) $exception->getResponse()->getBody();
            $exception = json_decode($exception);
            return $exception;
        }
    }
}
