<?php namespace Sheba\Payment\Methods\Nagad;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Sheba\Payment\Methods\Nagad\Response\Initialize;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

class Inputs
{
    /** @var NagadStore $store */
    private $store;

    public static function headers(): array
    {
        return self::makeHeaders([
            'Content-Type' => 'application/json',
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4' => request()->ip(),
            'X-KM-Client-Type' => 'MOBILE_WEB'
        ]);
    }

    private static function makeHeaders(array $getHeaders): array
    {
        $headers = [];
        foreach ($getHeaders as $key => $header) {
            array_push($headers, "$key:$header");
        }
        return $headers;
    }

    /**
     * @param $transaction_id
     * @param NagadStore $store
     * @return array
     */
    public static function init($transaction_id, NagadStore $store): array
    {
        return self::data($transaction_id, $store);
    }

    /**
     * @param $transaction_id
     * @param Initialize $init
     * @param $amount
     * @param $call_back_url
     * @param NagadStore $store
     * @param $description
     * @return array
     */
    public static function complete($transaction_id, Initialize $init, $amount, $call_back_url, NagadStore $store, $description): array
    {
        Log::info(["description", $description]);
        $merchant_additional_info = json_encode(
            [
                "Service Name" =>"Sheba.xyz",
                "Purpose" =>  null
            ]
        );
//        $merchant_additional_info = '{"Service Name": "Sheba.xyz"}';
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
        Log::info(["returned data", [$payment_data, $store_data]]);
        return [$payment_data, $store_data];
    }

    /**
     * @param $transaction_id
     * @param NagadStore $store
     * @return array
     */
    private static function data($transaction_id, NagadStore $store): array
    {
        $date = Carbon::now()->format('YmdHis');
        $payment_data = [
            'merchantId' => $store->getMerchantId(),
            'orderId' => $transaction_id,
            'datetime' => $date,
            'challenge' => self::generateRandomString(40)
        ];
        $store_data = ['storeType' => class_basename($store)];

        return [$payment_data, $store_data];
    }

    /**
     * @param int $length
     * @return string
     */
    private static function generateRandomString(int $length = 40): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function orderID(): string
    {
        try {
            return 'S' . time() . randomString(4, 1, 1);
        } catch (\Exception $e) {
            return 'SHEBA' . time();
        }
    }

    public function setStore(NagadStore $store): Inputs
    {
        $this->store = $store;
        return $this;
    }
}
