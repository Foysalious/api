<?php namespace Sheba\TPProxy;


class TPRequest
{
    const METHOD_GET = "get";
    const METHOD_POST = "post";

    private $url;
    private $method;
    private $input = [];
    private $headers = [];

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return TPRequest
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return strtoupper($this->method);
    }

    /**
     * @param string $method = "get" | "post"
     * @return TPRequest
     */
    public function setMethod($method)
    {
        if (!in_array($method, [self::METHOD_GET, self::METHOD_POST])) {
            throw new \InvalidArgumentException("$method not supported by tp client");
        }
        $this->method = $method;
        return $this;
    }

    /**
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param array $input
     * @return TPRequest
     */
    public function setInput(array $input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return TPRequest
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }
}
