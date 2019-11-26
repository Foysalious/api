<?php namespace Sheba\Exceptions;

use App\Exceptions\ApiValidationException;
use Exception as BaseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Exceptions\Handlers\ApiValidationExceptionHandler;
use Sheba\Exceptions\Handlers\Handler;
use Sheba\Exceptions\Handlers\ThrowableHandler;
use Sheba\Exceptions\Handlers\ValidationExceptionHandler;
use Throwable;

class HandlerFactory
{
    /**
     * @param Request $request
     * @param BaseException $e
     * @return Handler|null
     */
    public static function get(Request $request, BaseException $e)
    {
        $handler = self::getHandler($e);
        if(is_null($handler)) return null;

        return $handler->setException($e)->setRequest($request);
    }

    /**
     * @param BaseException $e
     * @return Handler
     */
    private static function getHandler(BaseException $e)
    {
        if($e instanceof ValidationException) return app(ValidationExceptionHandler::class);
        if($e instanceof ApiValidationException) return app(ApiValidationExceptionHandler::class);
        if($e instanceof Throwable) return app(ThrowableHandler::class);
        return null;
    }
}
