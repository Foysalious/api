<?php namespace Sheba\TopUp\Bulk\Exception;

use App\Exceptions\DoNotReportException;
use Throwable;

class InvalidTopupData extends DoNotReportException
{
    private $excelErrorsFileLink;

    /**
     * InvalidTotalAmount constructor.
     *
     * @param $excel_errors_file_link
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($excel_errors_file_link, $message = 'Check The Excel Data Format Properly.', $code = 400, Throwable $previous = null)
    {
        $this->excelErrorsFileLink = $excel_errors_file_link;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getExcelErrorsFileLink()
    {
        return $this->excelErrorsFileLink;
    }
}
