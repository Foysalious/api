<?php namespace Sheba\AccessToken\Exception;


use App\Exceptions\ApiValidationException;
use Throwable;

class AccessTokenDoesNotExist extends ApiValidationException
{
    public function __construct($message = "Your session has expired. Try Login", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}