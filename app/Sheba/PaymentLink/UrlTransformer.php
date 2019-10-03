<?php namespace Sheba\PaymentLink;


class UrlTransformer
{
    private $response;

    /**
     * @param \stdClass $response
     * @return $this
     */
    public function setResponse(\stdClass $response)
    {
        $this->response = $response;
        return $this;
    }

    public function getShortUrl()
    {
        return $this->response->shortUrl;
    }
}