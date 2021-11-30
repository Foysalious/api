<?php namespace Sheba\TPProxy;

use InvalidArgumentException;

class TPRequest
{
    const METHOD_GET = "get";
    const METHOD_POST = "post";

    private $url;
    private $method;
    private $input = [];
    private $headers = [];
    private $readTimeout = 60;
    private $connectTimeout = 60;
    private $timeout = 60;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'url' => $this->getUrl(),
            'headers' => $this->getHeaders(),
            'input' => $this->getInput(),
            'method' => $this->getMethod()
        ];
    }

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
    public function setUrl($url): TPRequest
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return TPRequest
     */
    public function setHeaders(array $headers): TPRequest
    {
        $this->headers = $headers;
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
    public function setInput(array $input): TPRequest
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($this->method);
    }

    /**
     * @param string $method = "get" | "post"
     * @return TPRequest
     */
    public function setMethod(string $method): TPRequest
    {
        if (!in_array($method, [self::METHOD_GET, self::METHOD_POST])) {
            throw new InvalidArgumentException("$method not supported by tp client");
        }
        $this->method = $method;

        return $this;
    }

    /**
     * @return int
     */
    public function getReadTimeout(): int
    {
        return $this->readTimeout;
    }

    /**
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return TPRequest
     */
    public function setTimeout(int $timeout): TPRequest
    {
        $this->timeout = $timeout;
        $this->readTimeout = $timeout;
        $this->connectTimeout = $timeout;

        return $this;
    }
}
