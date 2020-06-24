<?php namespace Sheba\Transactions;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use stdClass;
use Throwable;

class WalletClient
{
    /** @var HttpClient $httpClient */
    private $httpClient;
    private $baseWalletUrl;

    public function __construct(HttpClient $http_client)
    {
        $this->httpClient = $http_client;
        $this->baseWalletUrl = config('sheba.wallet_url') . '/api/';
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
            $response = $this->httpClient->request('POST', $uri, $params)->getBody()->getContents();
            return json_decode($response);
        } catch (Throwable $exception) {
            $exception = (string) $exception->getResponse()->getBody();
            $exception = json_decode($exception);
            return $exception;
        }
    }
}
