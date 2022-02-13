<?php namespace App\Exceptions\Pos;


use App\Exceptions\DoNotReportException;
use Throwable;

class DataMigrationException extends DoNotReportException
{
    public function __construct($message = 'Your data are already migrated or in progress', $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}