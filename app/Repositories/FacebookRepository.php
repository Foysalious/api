<?php

namespace App\Repositories;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FacebookRepository
{
    public function verifyAccessToken($token, $fb_id)
    {
        return true;
        try {
            $client = new Client();
            $res = $client->request('GET', 'https://graph.facebook.com/me?fields=id&access_token=' . $token);
            $data = json_decode($res->getBody(), true);
            return $data['id'] == $fb_id ? true : false;
        } catch (RequestException $e) {
            return false;
        }

    }
}