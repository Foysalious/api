<?php

namespace Sheba\Payment\Methods\OkWallet;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Sheba\Payment\Methods\OkWallet\Exception\FailedToInitiateException;
use Sheba\Payment\Methods\OkWallet\Exception\KeyEncryptionFailed;
use Sheba\Payment\Methods\OkWallet\Response\InitResponse;

class OkWalletClient {
    private $baseUrl, $account, $apiKey, $apiSecret, $format, $client, $public_key, $merchant;

    public function __construct() {
        $this->baseUrl    = config('payment.ok_wallet.base_url');
        $this->account    = config('payment.ok_wallet.account');
        $this->apiKey     = config('payment.ok_wallet.api_key');
        $this->apiSecret  = config('payment.ok_wallet.api_secret');
        $this->format     = config('payment.ok_wallet.format', 'json');
        $this->merchant   = config('payment.ok_wallet.merchant', 'sheba.xyz');
        $this->client     = new Client();
        $this->public_key = file_get_contents(resource_path(config('payment.ok_wallet.key_path')));
    }

    public static function getTransactionUrl($sessionKey) {
        $cls = (new OkWalletClient());
        return "$cls->baseUrl/okTransaction/$sessionKey";
    }

    /**
     * @param $amount
     * @param $trx_id
     * @return InitResponse
     * @throws FailedToInitiateException
     * @throws KeyEncryptionFailed
     */
    public function createSession($amount, $trx_id) {
        try {
            $data     = [
                'format' => $this->format,
                'key'    => $this->encrypt_value($this->apiKey),
                'secret' => $this->encrypt_value($this->apiSecret),
                'amount' => $this->encrypt_value(doubleval($amount)),
                'json'   => json_encode([
                    'TRXNID'   => $trx_id,
                    'CHARGE'   => 0,
                    'MERCHANT' => $this->merchant,
                    "AMOUNT"   => doubleval($amount),
                    'SECRET'   => $this->apiSecret,
                    'KEY'      => $this->apiKey,
                    'DESC'     => "SHEBA OK WALLET TRANSACTION"
                ])
            ];
            $response = $this->client->post("$this->baseUrl/createSession/", $this->getOptions($data))->getBody()->getContents();
            return new InitResponse(json_decode($response, true));
        } catch (ClientException $e) {
            throw new FailedToInitiateException($e->getMessage());
        }
    }

    /**
     * @param $value
     * @return string
     * @throws KeyEncryptionFailed
     */
    private function encrypt_value($value) {
        if (!$public = openssl_get_publickey($this->public_key)) {
            //Invalid x509 certificate;
            throw new KeyEncryptionFailed("Invalid Certificate for key encryption");
        }
        if (!openssl_public_encrypt($value, $crypted, $public)) {
            //OpenSSL public key encryption failed
            throw new KeyEncryptionFailed();
        }
        $value = base64_encode($crypted);
        return $value;
    }

    private function getOptions($data = null) {
        $options['form_params'] = $data;
        return $options;
    }

    /**
     * @param $transaction_id
     * @return mixed
     */
    public function validationRequest($transaction_id) {
        $response = $this->client->post("$this->baseUrl/getTransaction/$this->apiKey/$transaction_id")->getBody()->getContents();
        return json_decode($response, true);

    }
}
