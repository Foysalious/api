<?php

namespace Tests\Mocks;

use Mockery\CountValidator\Exception;
use Sheba\OAuth2\AccountServerClient;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
class MockAccountServerClient extends AccountServerClient
{
    public static $token;

    public function post($uri, $data, $headers = null)
    {
        if ($uri == 'api/v3/profile/login') {
            return $this->getLoginData($data);
        }

        return self::$token;
    }

    private function getLoginData($data)
    {
        if ($data['email'] != 'tisha@sheba.xyz' || $data['password'] != '12345') {
            throw new Exception("Data Mismatch");
        }

        return json_decode(
            '{
            "code": 200,
            "message": "Successful",
            "token": "'.self::$token.'",
            "user": {
                "name": "Tiara Wuckert",
                "mobile": "+8801678242955",
                "image": "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg",
                "business_id": 1,
                "business_name": "My Company",
                "is_remote_attendance_enable": true
            }
            }',
            true
        );
    }
}
