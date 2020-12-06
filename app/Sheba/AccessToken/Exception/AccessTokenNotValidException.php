<?php namespace Sheba\AccessToken\Exception;

use App\Exceptions\ApiValidationException;
use Throwable;

class AccessTokenNotValidException extends ApiValidationException
{
    public function __construct($message = "Access token is not valid.", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
