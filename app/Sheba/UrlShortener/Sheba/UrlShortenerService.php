<?php

namespace App\Sheba\UrlShortener\Sheba;

use Sheba\UrlShortener\Sheba\UrlShortenerServerClient;

class UrlShortenerService
{
    private $urlShortenerClient;

    public function __construct(UrlShortenerServerClient $client)
    {
        $this->urlShortenerClient = $client;
    }

    public function shortUrl($url)
    {
        $response = $this->urlShortenerClient->post('generate-short-url',[
            'url' => $url
        ]);
        return $response['short_url'];
    }
}