<?php


namespace Sheba\Payment\Methods\Nagad;


use Carbon\Carbon;
use Sheba\Payment\Methods\Nagad\Exception\EncryptionFailed;
use Sheba\Payment\Methods\Nagad\Response\Initialize;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

class Inputs
{
    /**
     * @var NagadStore
     */
    private $store;

    public function setStore(NagadStore $store)
    {
        $this->store = $store;
        return $this;
    }

    public static function headers()
    {
        return self::makeHeaders([
            'Content-Type'     => 'application/json',
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4'       => request()->ip(),
            'X-KM-Client-Type' => 'MOBILE_WEB'
        ]);
    }

    static function makeHeaders(array $getHeaders)
    {
        $headers = [];
        foreach ($getHeaders as $key => $header) {
            array_push($headers, "$key:$header");
        }
        return $headers;
    }

    static function get_client_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * @param            $transactionID
     * @param NagadStore $store
     * @return array
     * @throws EncryptionFailed
     */
    public static function init($transactionID, NagadStore $store)
    {
        return self::data($transactionID, $store);
    }

    /**
     * @param            $transactionId
     * @param Initialize $init
     * @param            $amount
     * @param            $callbackUrl
     * @param NagadStore $store
     * @return array
     * @throws EncryptionFailed
     */
    public static function complete($transactionId, Initialize $init, $amount, $callbackUrl, NagadStore $store)
    {
        $merchantAdditionalInfo = '{"Service Name": "Sheba.xyz"}';
        $data                   = json_encode(['merchantId' => $store->getMerchantId(), 'orderId' => $transactionId, 'amount' => $amount, 'currencyCode' => '050', 'challenge' => $init->getChallenge()]);
        return ['sensitiveData' => self::getEncoded($data, $store), 'signature' => self::generateSignature($data, $store), 'merchantCallbackURL' => $callbackUrl, 'additionalMerchantInfo' => json_decode($merchantAdditionalInfo)];
    }

    /**
     * @param string     $data
     * @param NagadStore $store
     * @return string
     * @throws EncryptionFailed
     */
    static function getEncoded($data, NagadStore $store)
    {
        $key = openssl_get_publickey($store->getPublicKey());
        if (!openssl_public_encrypt($data, $encrypted, $key)) throw new EncryptionFailed();
        return base64_encode($encrypted);
    }

    /**
     * @param            $transactionId
     * @param NagadStore $store
     * @return array
     * @throws EncryptionFailed
     */
    private static function data($transactionId, NagadStore $store)
    {
        $date = Carbon::now()->format('YmdHis');
        $data = json_encode(['merchantId' => $store->getMerchantId(), 'orderId' => $transactionId, 'datetime' => $date, 'challenge' => self::generateRandomString(40)]);
        return ['sensitiveData' => self::getEncoded($data, $store), 'signature' => self::generateSignature($data, $store), 'dateTime' => $date];
    }

    private static function generateRandomString($length = 40)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static function generateSignature($data, NagadStore $store)
    {
        $private_key = $store->getPrivateKey();
        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    static function orderID()
    {
        return 'SHEBA' . time();
    }
}
