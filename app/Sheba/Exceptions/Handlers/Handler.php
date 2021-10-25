<?php namespace Sheba\Exceptions\Handlers;

use Throwable;
use Illuminate\Http\Request;

abstract class Handler
{
    /** @var Throwable */
    protected $exception;
    /** @var Request */
    protected $request;

    /**
     * @param mixed $exception
     * @return Handler
     */
    public function setException(Throwable $exception)
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
        $response = [
            'code' => $this->getCode(),
            'message' => $this->getMessage()
        ];

        if ($this->wantsTrace()) {
            if ($this->wantsToDie()) dd($this->exception);

            $response['exception'] = [
                'message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'trace' => simplifyExceptionTrace($this->exception)
            ];
        }
        return response()->json($response);
    }

    public function report()
    {
        logError($this->exception);
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
        return ($this->request->has('debug') && $this->request->debug) || config('app.env') != 'production';
    }

    protected function wantsToDie()
    {
        return $this->request->has('die') && $this->request->die;
    }
}
