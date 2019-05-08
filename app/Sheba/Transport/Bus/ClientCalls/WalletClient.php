<?php namespace Sheba\Transport\Bus\ClientCalls;

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
        $this->baseWalletUrl = config('sheba.wallet_url') . '/api/';
    }

    /**
     * @param $wallet_id
     * @param $amount
     * @param $type
     * @param null $transaction_details
     * @param null $source_type
     * @param null $log
     * @return mixed
     * @throws GuzzleException
     * @throws Throwable
     */
    public function saveTransaction($wallet_id, $amount, $type, $transaction_details = null, $source_type = null, $log = null)
    {
        try {
            $url = $this->baseWalletUrl . 'transaction';
            $params = ['form_params' => ['wallet_id' => $wallet_id, 'amount' => $amount, 'type' => $type]];

            if (isset($transaction_details)) {
                $params['form_params']['transaction_details'] = $transaction_details;
            }
            if (isset($source_type) && $source_type) {
                $params['form_params']['source_type'] = $source_type;
            }

            $result = $this->client->request('POST', $url, $params)->getBody()->getContents();

            return json_decode($result);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param null $walletId
     * @return stdClass
     */
    public function getDetails($walletId = null)
    {
        try {
            $uri = $walletId ? $this->baseWalletUrl . 'wallet/show?wallet_id=' . $walletId : $this->baseWalletUrl . '/api/wallet';
            $result = json_decode($this->client->get($uri)->getBody()->getContents());
            return ($result && $result->code == 200) ? $result->wallet : $this->defaultWallet($walletId);
        } catch (Throwable $exception) {
            return $this->defaultWallet($walletId);
        }
    }

    /**
     * @param $walletId
     * @return stdClass
     */
    private function defaultWallet($walletId)
    {
        $wallet = new stdClass();
        $wallet->wallet_id = $walletId;
        $wallet->details = [
            "name" => " Wallet",
            "balance" => 0,
        ];
        return $wallet;
    }

    /**
     * @param $wallet_id
     * @param $page
     * @param $limit
     * @param null $type
     * @return array|mixed
     */
    public function getTransactions($wallet_id, $page, $limit, $type = null)
    {
        try {
            $queryString = '';
            if ($type) {
                $queryString .= '&type=' . $type;
            }
            $uri = $this->baseWalletUrl . 'transaction?wallet_number=' . $wallet_id . '&page=' . $page . '&limit=' . $limit . $queryString;
            $result = json_decode($this->client->get($uri)->getBody()->getContents());
            return $result && $result->code == 200 ? $result : $this->getDefaultTransactionsData();
        } catch (Throwable $exception) {
            return $this->getDefaultTransactionsData();
        }
    }

    /**
     * @return array
     */
    private function getDefaultTransactionsData()
    {
        return [
            'data' => [],
            'pagination' =>
                [
                    'total' => 0,
                    'count' => 0,
                    'per_page' => 0,
                    'current_page' => 0,
                    'total_pages' => 0
                ]
        ];
    }

    /**
     * @param $transaction_id
     * @return mixed|stdClass
     */
    public function validateTransaction($transaction_id)
    {
        $uri = $this->baseWalletUrl . 'transaction/validate?transaction_id=' . $transaction_id;
        try {
            $response = json_decode($this->client->get($uri)->getBody()->getContents());
            return $response;
        } catch (Throwable $exception) {
            $code = new stdClass();
            $code->code = 500;
            return $code;
        }
    }

    public function getEarnings($wallet_id)
    {
        $uri = $this->baseWalletUrl . 'wallet/' . $wallet_id . '/earnings';
        try {
            return json_decode($this->client->get($uri)->getBody()->getContents());
        } catch (Throwable $e) {
            throw $e;
        }
    }
}
