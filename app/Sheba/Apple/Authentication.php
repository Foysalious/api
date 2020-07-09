<?php namespace Sheba\Apple;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Namshi\JOSE\JWS;
use RuntimeException;

class Authentication
{
    private $keyId;
    private $teamId;
    private $clientId;

    public function __construct()
    {
        $this->keyId = config('apple.key_id');
        $this->teamId = config('apple.team_id');
        $this->clientId = config('apple.client_id');
    }

    /**
     * @param $code
     * @return AuthenticationResponse
     */
    public function getUser($code)
    {
        $client = new Client();
        try {
            $response = $client->request('POST', 'https://appleid.apple.com/auth/token',
                [
                    'form_params' => [
                        'client_id' => 'xyz.sheba.app',
                        'client_secret' => $this->generateJWT(),
                        'code' => $code,
                        'grant_type' => 'authorization_code'
                    ],
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ]
                ]);
            $data = json_decode($response->getBody(), 1);
            $payload = JWS::load($data['id_token'])->getPayload();
            return (new AuthenticationResponse())->setCode(200)->setEmail($payload['email'])->setEmailVerified($payload['email_verified']);
        } catch (GuzzleException $e) {
            return (new AuthenticationResponse())->setCode(500);
        }
    }

    public function encode($data)
    {
        $encoded = strtr(base64_encode($data), '+/', '-_');
        return rtrim($encoded, '=');
    }

    public function generateJWT()
    {
        $header = [
            'alg' => 'ES256',
            'kid' => $this->keyId
        ];
        $body = [
            'iss' => $this->teamId,
            'iat' => time(),
            'exp' => time() + 3600,
            'aud' => 'https://appleid.apple.com',
            'sub' => $this->clientId
        ];
        $privKey = openssl_pkey_get_private(file_get_contents(storage_path('AuthKey_Q66N9Y2HF6.pem')));
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