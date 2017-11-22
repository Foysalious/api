<?php

namespace App\Repositories;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FacebookRepository
{
    public function verifyAccessToken($token, $fb_id)
    {
        try {
            $client = new Client();
            $res = $client->request('GET', 'https://graph.facebook.com/me?fields=id,picture.height(400).width(400)&access_token=' . $token);
            $data = json_decode($res->getBody(), true);
            return $data['id'] == $fb_id ? $data['picture']['data']['url'] : false;
        } catch (RequestException $e) {
            return false;
        }

    }
}