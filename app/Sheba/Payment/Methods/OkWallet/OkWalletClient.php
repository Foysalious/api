<?php namespace Sheba\Payment\Methods\OkWallet;

use Sheba\Payment\Methods\OkWallet\Exception\FailedToInitiateException;
use Sheba\Payment\Methods\OkWallet\Exception\KeyEncryptionFailed;
use Sheba\Payment\Methods\OkWallet\Response\InitResponse;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;

class OkWalletClient
{
    /** @var TPProxyClient */
    private $tpClient;
    private $baseUrl, $account, $apiKey, $apiSecret, $format, $publicKey, $merchant;

    public function __construct(TPProxyClient $tp_client)
    {
        $this->baseUrl    = config('payment.ok_wallet.base_url');
        $this->account    = config('payment.ok_wallet.account');
        $this->apiKey     = config('payment.ok_wallet.api_key');
        $this->apiSecret  = config('payment.ok_wallet.api_secret');
        $this->format     = config('payment.ok_wallet.format', 'json');
        $this->merchant   = config('payment.ok_wallet.merchant', 'sheba.xyz');
        $this->publicKey  = file_get_contents(resource_path(config('payment.ok_wallet.key_path')));
        $this->tpClient   = $tp_client;
    }

    public static function getTransactionUrl($sessionKey)
    {
        $cls = app(OkWalletClient::class);
        return "$cls->baseUrl/okTransaction/$sessionKey";
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
            $request = (new TPRequest())
                ->setMethod(TPRequest::METHOD_POST)
                ->setUrl("$this->baseUrl/createSession/")
                ->setInput([
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
                ]);
            $response = $this->tpClient->call($request);
            return new InitResponse((array) $response);
        } catch (TPProxyServerError $e) {
            throw new FailedToInitiateException($e->getMessage());
        }
    }

    /**
     * @param $value
     * @return string
     * @throws KeyEncryptionFailed
     */
    private function encrypt_value($value)
    {
        if (!$public = openssl_get_publickey($this->publicKey)) throw new KeyEncryptionFailed("Invalid Certificate for key encryption");
        if (!openssl_public_encrypt($value, $encrypted, $public)) throw new KeyEncryptionFailed();
        $value = base64_encode($encrypted);
        return $value;
    }

    /**
     * @param $transaction_id
     * @return array
     * @throws TPProxyServerError
     */
    public function validationRequest($transaction_id)
    {
        $request = (new TPRequest())
            ->setMethod(TPRequest::METHOD_POST)
            ->setUrl("$this->baseUrl/getTransaction/$this->apiKey/$transaction_id");
        $response = $this->tpClient->call($request);
        return (array) $response;

    }
}
