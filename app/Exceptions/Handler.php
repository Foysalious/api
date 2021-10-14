<?php namespace App\Exceptions;

use App\Sheba\Release\Release;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Sheba\Exceptions\HandlerFactory;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Subscription\Partner\Access\Exceptions\AccessRestrictedExceptionForPackage;
use Sheba\TopUp\Exception\PinMismatchException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        InitiateFailedException::class,
        AccessRestrictedExceptionForPackage::class,
        PinMismatchException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        /**
         * Done in the render section.
         * As, request is needed sometimes.
         */
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $e
     * @return \Illuminate\Http\Response|Response
     */
    public function render($request, Exception $e)
    {

        $handler = HandlerFactory::get($request, $e);

        if ($this->shouldReport($e)) $handler ? $handler->report() : logError($e);

        return $handler ? $handler->render() : parent::render($request, $e);
    }
}
