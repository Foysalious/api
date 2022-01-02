<?php namespace App\Exceptions;

use Dingo\Api\Http\Request;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Sheba\Exceptions\HandlerFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Dingo\Api\Exception\Handler as DingoHandler;

class CustomHandler extends DingoHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        AllarKosomWillNotReportException::class
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
     * @throws Exception
     */
    public function render($request, Exception $e)
    {
        $handler = HandlerFactory::get($request, $e);

        if ($this->parentHandler->shouldReport($e)) $handler ? $handler->report() : logError($e);

        return $handler ? $handler->render() : parent::render($request, $e);
    }
}
