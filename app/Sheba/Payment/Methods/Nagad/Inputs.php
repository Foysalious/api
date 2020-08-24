<?php


namespace Sheba\Payment\Methods\Nagad;


use Carbon\Carbon;
use Sheba\Payment\Methods\Nagad\Exception\EncryptionFailed;
use Sheba\Payment\Methods\Nagad\Response\Initialize;

class Inputs
{
    private $merchantId;

    public static function headers()
    {
        return [
            'Content-Type:application/json',
            'X-KM-Api-Version:v-0.2.0',
            'X-KM-IP-V4:'.request()->ip(),
            'X-KM-Client-Type:MOBILE_WEB'
        ];
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
     * @param $transactionID
     * @return array
     * @throws EncryptionFailed
     */
    public static function init($transactionID)
    {
        return self::data($transactionID);
    }

    /**
     * @param            $transactionId
     * @param Initialize $init
     * @param            $amount
     * @param            $callbackUrl
     * @return array
     * @throws EncryptionFailed
     */
    public static function complete($transactionId, Initialize $init, $amount, $callbackUrl)
    {
        $data = json_encode(['merchantId' => config('nagad.merchant_id'), 'orderId' => $transactionId, 'amount' => $amount, 'currencyCode' => '050', 'challenge' => $init->getChallenge()]);
        return ['sensitiveData' => self::getEncoded($data), 'signature' => self::generateSignature($data), 'merchantCallbackURL' => $callbackUrl];
    }

    /**
     * @param string $data
     * @return string
     * @throws EncryptionFailed
     */
    static function getEncoded($data)
    {
        $key = openssl_get_publickey(file_get_contents(config('nagad.public_key_path')));
        if (!openssl_public_encrypt($data, $encrypted, $key)) throw new EncryptionFailed();
        return base64_encode($encrypted);
    }

    /**
     * @param $transactionId
     * @return array
     * @throws EncryptionFailed
     */
    private static function data($transactionId)
    {
        $date = Carbon::now()->format('YmdHis');
        $data = json_encode(['merchantId' => config('nagad.merchant_id'), 'orderId' => $transactionId, 'datetime' => $date, 'challenge' => self::generateRandomString(40)]);
        return ['sensitiveData' => self::getEncoded($data), 'signature' => self::generateSignature($data), 'dateTime' => $date];
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

    static function generateSignature($data)
    {
        $private_key = file_get_contents(config('nagad.private_key_path'));
        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    static function orderID()
    {
        return 'SHEBA' . time();
    }
}
