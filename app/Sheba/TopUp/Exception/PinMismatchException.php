<?php namespace Sheba\TopUp\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class PinMismatchException extends DoNotReportException
{
    private $wrongPinCount;

    /**
     * PinMismatchException constructor.
     * @param $wrong_pin_count
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($wrong_pin_count, $message = "Pin Mismatch", $code = 403, Throwable $previous = null)
    {
        $this->wrongPinCount = $wrong_pin_count;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getWrongPinCount()
    {
        return $this->wrongPinCount;
    }
}
