<?php namespace Sheba\Resource\Jobs\Service;


use App\Exceptions\ApiValidationException;
use Throwable;

class ServiceExistsInOrderException extends ApiValidationException
{
    public function __construct($message = 'This service is already added in your order.', $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}