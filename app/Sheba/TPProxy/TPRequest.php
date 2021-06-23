<?php namespace Sheba\TPProxy;

class TPRequest
{
    const METHOD_GET  = "get";
    const METHOD_POST = "post";

    private $url;
    private $method;
    private $input           = [];
    private $headers         = [];
    private $read_timeout    = 60;
    private $connect_timeout = 60;
    private $timeout         = 60;

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
     * @return array|false|string
     */
    public function getInput()
    {
        if (!empty($this->headers) && in_array('Content-Type:application/json', $this->headers)) return json_encode($this->input);
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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'url'     => $this->getUrl(),
            'headers' => $this->getHeaders(),
            'input'   => $this->getInput(),
            'method'  => $this->getMethod()
        ];
    }

    /**
     * @return int
     */
    public function getReadTimeout()
    {
        return $this->read_timeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->connect_timeout;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return TPRequest
     */
    public function setTimeout($timeout)
    {
        $this->timeout         = $timeout;
        $this->read_timeout    = $timeout;
        $this->connect_timeout = $timeout;
        return $this;
    }


}
