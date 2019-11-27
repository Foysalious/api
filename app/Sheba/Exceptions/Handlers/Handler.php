<?php namespace Sheba\Exceptions\Handlers;

use Exception;
use Illuminate\Http\Request;

abstract class Handler
{
    /** @var Exception */
    protected $exception;
    /** @var Request */
    protected $request;

    /**
     * @param mixed $exception
     * @return Handler
     */
    public function setException(Exception $exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * @param mixed $request
     * @return Handler
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function render()
    {
        return api_response($this->request, null, $this->getCode(), [
            'code' => $this->getCode(),
            'message' => $this->getMessage()
        ]);
    }

    /**
     * @return string
     */
    abstract protected function getMessage();

    /**
     * @return int
     */
    abstract protected function getCode();

    protected function wantsTrace()
    {
        return ($this->request->has('debug') && $this->request->debug) || config('app.env') == 'local';
    }
}
