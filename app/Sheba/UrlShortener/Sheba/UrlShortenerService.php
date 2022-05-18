<?php

namespace App\Sheba\UrlShortener\Sheba;

use Exception;
use Sheba\UrlShortener\Sheba\UrlShortenerServerClient;

class UrlShortenerService
{
    private $urlShortenerClient;

    public function __construct(UrlShortenerServerClient $client)
    {
        $this->urlShortenerClient = $client;
    }

    /**
     * @throws UrlShortenerServerError
     */
    public function shortUrl($url)
    {
        try {
            $response = $this->urlShortenerClient->post('generate-short-url',[
                'url' => $url
            ]);
            return $response['short_url'];
        } catch (UrlShortenerServerError $error) {
            return '';
        } catch (Exception $e) {
            throw new $e;
        }
    }
}