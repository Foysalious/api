<?php


namespace Sheba\NeoBanking\Exceptions;


use Throwable;

class CategoryPostDataInvalidException extends NeoBankingException
{
    public function __construct($message = "Category Post Data Invalid", $code = 400, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
