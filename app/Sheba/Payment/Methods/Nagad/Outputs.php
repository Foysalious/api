<?php namespace Sheba\Payment\Methods\Nagad;

use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

class Outputs
{
    public static function decode($data, NagadStore $store)
    {
        $private_key = $store->getPrivateKey();
        openssl_private_decrypt(base64_decode($data), $plain_text, $private_key);
        return json_decode($plain_text, true);
    }
}
