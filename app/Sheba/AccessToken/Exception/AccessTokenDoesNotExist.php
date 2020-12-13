<?php namespace Sheba\AccessToken\Exception;


use App\Exceptions\ApiValidationException;
use Throwable;

class AccessTokenDoesNotExist extends ApiValidationException
{
    public function __construct($message = "Access token doesn't. exist", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}