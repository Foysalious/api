<?php namespace Sheba\Payment\Methods\Nagad;

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

    public static function headers()
    {
        return self::makeHeaders([
            'Content-Type' => 'application/json',
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4' => request()->ip(),
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
        if (isset($_SERVER['HTTP_CLIENT_IP'])) $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED'])) $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR'])) $ipaddress = $_SERVER['REMOTE_ADDR'];
        else $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    /**
     * @param $transaction_id
     * @param NagadStore $store
     * @return array
     * @throws EncryptionFailed
     */
    public static function init($transaction_id, NagadStore $store)
    {
        return self::data($transaction_id, $store);
    }

    /**
     * @param $transaction_id
     * @param \Sheba\Payment\Methods\Nagad\Response\Initialize $init
     * @param $amount
     * @param $call_back_url
     * @param \Sheba\Payment\Methods\Nagad\Stores\NagadStore $store
     * @return array
     */
    public static function complete($transaction_id, Initialize $init, $amount, $call_back_url, NagadStore $store): array
    {
        $merchant_additional_info = '{"Service Name": "Sheba.xyz"}';
        $payment_data = [
            'merchantId' => $store->getMerchantId(),
            'orderId' => $transaction_id,
            'amount' => $amount,
            'currencyCode' => '050',
            'challenge' => $init->getChallenge()
        ];

        $store_data = [
            'storeType' => class_basename($store),
            'merchantCallbackURL' => $call_back_url,
            'additionalMerchantInfo' => json_decode($merchant_additional_info)
        ];

        return [$payment_data, $store_data];
    }

    /**
     * @param $transaction_id
     * @param NagadStore $store
     * @return array
     * @throws EncryptionFailed
     */
    private static function data($transaction_id, NagadStore $store): array
    {
        $date = Carbon::now()->format('YmdHis');
        $payment_data = [
            'merchantId'=> $store->getMerchantId(),
            'orderId'   => $transaction_id,
            'datetime'  => $date,
            'challenge' => self::generateRandomString(40)
        ];

        $store_data = ['storeType' => class_basename($store)];

        return [$payment_data, $store_data];
    }

    /**
     * @param int $length
     * @return string
     */
    private static function generateRandomString($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * @param string $data
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

    static function generateSignature($data, NagadStore $store)
    {
        $private_key = $store->getPrivateKey();
        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    static function orderID()
    {
        try {
            return 'S' . time() . randomString(4, 1, 1);
        } catch (\Exception $e) {
            return 'SHEBA' . time();
        }
    }

    public function setStore(NagadStore $store)
    {
        $this->store = $store;
        return $this;
    }
}
