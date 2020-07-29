<?php


namespace Sheba\Payment\Methods\Nagad;


use Carbon\Carbon;
use Sheba\Payment\Methods\Nagad\Exception\EncryptionFailed;

class Inputs
{
    private $publicKey;
    private $merchantId;

    public function __construct()
    {
        $this->merchantId = config('nagad.merchant_id');
    }

    /**
     * @param $transactionID
     * @return array
     * @throws EncryptionFailed
     */
    public function init($transactionID)
    {
        $date = Carbon::now()->format('YmdHis');
        return ['dateTime' => $date, 'sensitiveData' => $this->sensitiveData($transactionID, $date)];
    }

    /**
     * @param array $data
     * @return string
     * @throws EncryptionFailed
     */
    static function getEncoded(array $data)
    {
        if (!$key = openssl_get_publickey(file_get_contents(config('nagad.public_key_path')))) throw new EncryptionFailed();
        if (!openssl_public_encrypt(json_encode($data), $encrypted, $key, OPENSSL_PKCS1_PADDING)) throw new EncryptionFailed();
        return base64_encode($encrypted);
    }

    /**
     * @param $transactionId
     * @param $date
     * @return string
     * @throws EncryptionFailed
     */
    private function sensitiveData($transactionId, $date)
    {
        $data = ['merchantId' => $this->merchantId, 'orderId' => $transactionId, 'dateTime' => $date, 'challenge' => bin2hex(openssl_random_pseudo_bytes(40))];
        return self::getEncoded($data);
    }
}
