<?php

namespace Sheba\Payment\Methods\OkWallet;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Sheba\Payment\Methods\OkWallet\Exception\FailedToInitiateException;
use Sheba\Payment\Methods\OkWallet\Exception\KeyEncryptionFailed;
use Sheba\Payment\Methods\OkWallet\Response\InitResponse;

class OkWalletClient
{
    private $baseUrl, $account, $apiKey, $apiSecret, $format, $client, $public_key;

    public function __construct()
    {
        $this->baseUrl    = config('OK_WALLET_BASE_URL', 'https://103.197.207.9/uat/okPay');
        $this->account    = config('OK_WALLET_ACCOUNT', '01799444000');
        $this->apiKey     = config('OK_WALLET_APP_KEY', 'opox9ps6pdfje4wchsejkqb7hd9de1qmh2');
        $this->apiSecret  = config('OK_WALLET_APP_SECRET', 'tf1rkktb7fttewbtik3m11ed7xmhhkgpogsojn2yt9w1j16v');
        $this->format     = config('OK_WALLET_API_FORMAT', 'json');
        $this->client     = new Client();
        $this->public_key = file_get_contents(resource_path('assets/ok-wallet/public_uat.key'));
    }

    public static function getTransactionUrl($sessionKey)
    {
        $cls = (new OkWalletClient());
        return "$cls->baseUrl/okPay/okTransaction/$sessionKey";
    }

    /**
     * @param $amount
     * @param $trx_id
     * @return InitResponse
     * @throws FailedToInitiateException
     * @throws KeyEncryptionFailed
     */
    public function createSession($amount, $trx_id)
    {
        try {
            $data     = [
                'format' => $this->format,
                'key'    => $this->encrypt_value($this->apiKey),
                'secret' => $this->encrypt_value($this->apiSecret),
                'amount' => $this->encrypt_value(doubleval($amount)),
                'json'   => json_encode([
                    'TRXNID'   => $trx_id,
                    'CHARGE'   => 0,
                    'MERCHANT' => "sheba.xyz",
                    "AMOUNT"   => doubleval($amount),
                    'SECRET'   => $this->apiSecret,
                    'KEY'      => $this->apiKey,
                    'DESC'     => "SHEBA OK WALLET TRANSACTION"
                ])
            ];
            $response = $this->client->post("$this->baseUrl/createSession/", $this->getOptions($data))->getBody()->getContents();
            dd($response);
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
    private function encrypt_value($value){
        if (!$public = openssl_get_publickey($this->public_key)) {
            //Invalid x509 certificate;
            throw new KeyEncryptionFailed("Invalid Certificate for key encryption");
        }
        if (!openssl_public_encrypt($value, $crypted, $public)) {
            //OpenSSL public key encryption failed
            throw new KeyEncryptionFailed();
        }
        $value=base64_encode($crypted);

        return $value;
    }
    private function getOptions($data = null)
    {
        $options['form_params'] = $data;
        return $options;
    }
}
