<?php namespace Sheba\Apple;


use RuntimeException;
use \Firebase\JWT\JWT;

class ClientSecret
{
    public function create()
    {
        $team_id = "497KZASBJJ";
        $kid = "Q66N9Y2HF6";
        $client_id = 'xyz.sheba.app';

        $key = file_get_contents(storage_path('AuthKey_Q66N9Y2HF6.p8'));
        $payload = array(
            "iss" => $team_id,
            "aud" => 'https://appleid.apple.com',
            'iat' => time(),
            'exp' => time() + 3600
        );

        /**
         * IMPORTANT:
         * You must specify supported algorithms for your application. See
         * https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
         * for a list of spec-compliant algorithms.
         */
        $token = JWT::encode($payload, $key, 'ES256', $kid);


//        $token = $this->generateJWT($kid, $team_id, $client_id);
        $data = [
            'client_id' => $client_id,
            'client_secret' => $token,
            'code' => 'c381bc28b67944bf68750b874203da702.0.nrxst.GU5Kc12u2VSNsC9VSPfBKQ',
            'grant_type' => 'authorization_code'
        ];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://appleid.apple.com/auth/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($ch);

        curl_close($ch);
        var_dump($serverOutput);
    }

    public function encode($data)
    {
        $encoded = strtr(base64_encode($data), '+/', '-_');
        return rtrim($encoded, '=');
    }

    public function generateJWT($kid, $iss, $sub)
    {
        $header = [
            'alg' => 'ES256',
            'kid' => $kid
        ];
        $body = [
            'iss' => $iss,
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $sub
        ];
        $privKey = openssl_pkey_get_private(file_get_contents(storage_path('AuthKey_FR4RDV4Z5H.pem')));
        if (!$privKey) {
            return false;
        }
        $payload = $this->encode(json_encode($header)) . '.' . $this->encode(json_encode($body));

        $signature = '';
        $success = openssl_sign($payload, $signature, $privKey, OPENSSL_ALGO_SHA256);
        if (!$success) return false;

        $raw_signature = self::fromDER($signature, 64);

        return $payload . '.' . $this->encode($raw_signature);
    }


    public static function fromDER($der, $partLength)
    {
        $hex = unpack('H*', $der)[1];
        if ('30' !== mb_substr($hex, 0, 2, '8bit')) { // SEQUENCE
            throw new RuntimeException();
        }
        if ('81' === mb_substr($hex, 2, 2, '8bit')) { // LENGTH > 128
            $hex = mb_substr($hex, 6, null, '8bit');
        } else {
            $hex = mb_substr($hex, 4, null, '8bit');
        }
        if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
            throw new RuntimeException();
        }
        $Rl = hexdec(mb_substr($hex, 2, 2, '8bit'));
        $R = self::retrievePositiveInteger(mb_substr($hex, 4, $Rl * 2, '8bit'));
        $R = str_pad($R, $partLength, '0', STR_PAD_LEFT);
        $hex = mb_substr($hex, 4 + $Rl * 2, null, '8bit');
        if ('02' !== mb_substr($hex, 0, 2, '8bit')) { // INTEGER
            throw new RuntimeException();
        }
        $Sl = hexdec(mb_substr($hex, 2, 2, '8bit'));
        $S = self::retrievePositiveInteger(mb_substr($hex, 4, $Sl * 2, '8bit'));
        $S = str_pad($S, $partLength, '0', STR_PAD_LEFT);
        return pack('H*', $R . $S);
    }


    private static function retrievePositiveInteger($data)
    {
        while ('00' === mb_substr($data, 0, 2, '8bit') && mb_substr($data, 2, 2, '8bit') > '7f') {
            $data = mb_substr($data, 2, null, '8bit');
        }
        return $data;
    }
}