<?php namespace Sheba\Logs;

use App\Sheba\Release\Release;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Throwable;

class ErrorLog
{
    /** @var Throwable */
    private $exception;
    /** @var Request */
    private $request;
    private $errorMessage;
    private $context;

    /** @var Hub */
    private $sentry;

    public function __construct()
    {
        $this->request = null;
        $this->errorMessage = null;
        $this->context = [];

        if (app()->bound('sentry')) $this->sentry = app('sentry');
    }

    public function setException(Throwable $exception): ErrorLog
    {
        $this->exception = $exception;
        return $this;
    }

    public function setRequest(Request $request): ErrorLog
    {
        $this->request = $request;
        return $this;
    }

    public function setErrorMessage($message): ErrorLog
    {
        $this->errorMessage = $message;
        return $this;
    }

    public function setExtra(array $extra): ErrorLog
    {
        if (!isAssoc($extra)) throw new InvalidArgumentException("Extra must be an associative array.");

        foreach ($extra as $key => $value) {
            $this->context[$key] = $value;
        }

        return $this;
    }

    public function send()
    {
        if ($this->sentry == null) return;

        if ($this->request) $this->context['request'] = $this->request->all();
        if ($this->errorMessage) $this->context['message'] = $this->errorMessage;
        if (count($this->context) > 0) {
            $this->sentry->configureScope(function (Scope $scope) {
                foreach ($this->context as $key => $value) {
                    $scope->setContext($key, $value);
                }
            });
        }

        /*$version = (new Release())->get();
        if ($version) $this->sentry->setRelease($version);*/

        $this->sentry->captureException($this->exception);
    }

}
