<?php namespace App\Exceptions\Pos;


use App\Exceptions\DoNotReportException;
use Throwable;

class DataAlreadyMigratedException extends DoNotReportException
{
    public function __construct($message = 'Your Data Are Already Migrated', $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}