<?php namespace Sheba\TopUp\Bulk\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class InvalidTopupData extends DoNotReportException
{
    private $excelErrors;

    /**
     * InvalidTotalAmount constructor.
     * @param $excel_errors
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($excel_errors, $message = 'Check The Excel Data Format Properly.', $code = 400, Throwable $previous = null)
    {
        $this->excelErrors = $excel_errors;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getExcelErrors()
    {
        return $this->excelErrors;
    }
}
