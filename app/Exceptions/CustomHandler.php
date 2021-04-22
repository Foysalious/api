<?php namespace App\Exceptions;

use Dingo\Api\Http\Request;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
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
        HttpException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e)
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
     * @param Throwable $e
     * @return \Illuminate\Http\Response|Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        $handler = HandlerFactory::get($request, $e);

        if ($this->parentHandler->shouldReport($e)) $handler ? $handler->report() : logError($e);

        return $handler ? $handler->render() : parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     * @param AuthenticationException $exception
     * @return \Illuminate\Http\Response|Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }
}
