<?php


namespace Sheba\Payment\Methods\Nagad;


class Outputs
{
    public static function decode($data)
    {
        $private_key = file_get_contents(config('nagad.private_key_path'));
        openssl_private_decrypt(base64_decode($data), $plain_text, $private_key);
        return json_decode($plain_text,true);
    }
}
