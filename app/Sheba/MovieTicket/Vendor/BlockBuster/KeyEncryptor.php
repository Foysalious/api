<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 2/25/2019
 * Time: 12:00 PM
 */

namespace Sheba\MovieTicket\Vendor\BlockBuster;


class KeyEncryptor
{
    public function encrypt_cbc($str,$key) {
        //$key = $this->hex2bin($key);
        $iv = $key;
        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);
        mcrypt_generic_init($td, $key, $iv);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return bin2hex($encrypted);
    }
    public function decrypt_cbc($code,$key) {
        //$key = $this->hex2bin($key);
        $code = $this->hex2bin($code);
        $iv = $key;
        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $iv);
        mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $code);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return utf8_encode(trim($decrypted));
    }
    protected function hex2bin($hexdata)
    {
        $bindata = '';
        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }
}