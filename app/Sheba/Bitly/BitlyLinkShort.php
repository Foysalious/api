<?php

namespace App\Sheba\Bitly;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class BitlyLinkShort
{
    public function shortUrl($long_url)
    {
        $header = [
            'Authorization' => 'Bearer '.config('bitly.access_token'),
            'Content-Type'  => 'application/json',
        ];
        $baseUrl=config('bitly.url')."/shorten";
        $client=new Client();
        $request=new Request('POST',$baseUrl, $header, json_encode(['long_url' => $long_url]));
        $response=$client->send($request);
        $statusCode = $response->getStatusCode();
        if ($statusCode === Response::HTTP_FORBIDDEN)
        {
            throw new AccessDeniedException('Invalid access token');
        }
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['link'];
    }
}