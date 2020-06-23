<?php namespace Sheba\Logs;

use App\Sheba\Release\Release;
use Exception;
use Illuminate\Http\Request;

class ErrorLog
{
    private $exception;
    /** @var Request */
    private $request;
    private $errorMessage;
    private $context;

    public function __construct()
    {
        $this->request = null;
        $this->errorMessage = null;
        $this->context = [];
    }

    public function setException(Exception $exception)
    {
        $this->exception = $exception;
        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
        return $this;
    }

    public function send()
    {
        if (!app()->bound('sentry')) return;

        $sentry = app('sentry');
        if ($this->request) $this->context['request'] = $this->request->all();
        if ($this->errorMessage) $this->context['message'] = $this->errorMessage;
        if (count($this->context) > 0) $sentry->user_context($this->context);

        if ($version = (new Release())->get()) $sentry->setRelease($version);

        $sentry->captureException($this->exception);
    }

}
