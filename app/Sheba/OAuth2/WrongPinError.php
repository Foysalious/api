<?php namespace Sheba\OAuth2;

use App\Exceptions\DoNotReportException;
use Throwable;

class WrongPinError extends DoNotReportException
{
    private $wrongPinCount;
    private $remainingHours;

    /**
     * WrongPinError constructor.
     * @param $wrong_pin_count
     * @param $remaining_hours
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($wrong_pin_count, $remaining_hours, $message = "", $code = 403, Throwable $previous = null)
    {
        $this->wrongPinCount = $wrong_pin_count;
        $this->remainingHours = $remaining_hours;

        if (!$message || $message == "") {
            $message = 'Accounts server could not authenticate request.';
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getWrongPinCount()
    {
        return $this->wrongPinCount;
    }

    /**
     * @return mixed
     */
    public function getRemainingHours()
    {
        return $this->remainingHours;
    }
}
