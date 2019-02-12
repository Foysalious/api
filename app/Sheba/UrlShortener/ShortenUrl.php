<?php namespace Sheba\UrlShortener;

use GuzzleHttp\Exception\GuzzleException;
use Sheba\UrlShortener\Bitly\BitLy;

class ShortenUrl
{
    private $bitLy;

    public function __construct(BitLy $bitLy)
    {
        $this->bitLy = $bitLy;
    }

    public function shorten($domain, $long_url)
    {
        try {
            return $this->bitLy->post('/bitlinks', [
                'domain' => $domain,
                'title' => 'string',
                'long_url' => $long_url
            ]);
        } catch (GuzzleException $e) {
            return $e;
        }
    }
}