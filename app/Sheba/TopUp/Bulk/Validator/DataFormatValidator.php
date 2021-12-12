<?php namespace Sheba\TopUp\Bulk\Validator;

use App\Helper\BangladeshiMobileValidator;
use Exception;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Sheba\TopUp\TopUpExcelDataFormatError;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\TopUp\Bulk\Exception\InvalidTopupData;
use Sheba\TopUp\Bulk\Exception\InvalidTotalAmount;
use Sheba\TopUp\Bulk\ReadExcelAndProcessData;
use Sheba\TopUp\ConnectionType;
use Sheba\TopUp\OTF\OtfAmountCheck;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpExcel;

class DataFormatValidator extends Validator
{
    /** @var TopUpAgent $agent */
    private $agent;
    /** @var TopUpExcelDataFormatError $excelDataFormatError */
    private $excelDataFormatError;
    /** @var ReadExcelAndProcessData $excel */
    private $excel;
    private $request;
    /** @var mixed $data */
    private $data;
    /** @var mixed $total */
    private $total;
    /** @var string $bulkExcelCdnFilePath */
    private $bulkExcelCdnFilePath;
    /** @var OtfAmountCheck */
    private $otfAmountCheck;

    public function __construct()
    {
        $this->excelDataFormatError = app(TopUpExcelDataFormatError::class);
        $this->excel = app(ReadExcelAndProcessData::class);
    }

    /**
     * @param TopUpAgent $agent
     * @return $this
     */
    public function setAgent(TopUpAgent $agent): DataFormatValidator
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request): DataFormatValidator
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return bool
     * @throws InvalidTopupData
     * @throws InvalidTotalAmount
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws Exception
     */
    public function check(): bool
    {
        $this->excel->setAgent($this->agent)->setExcel($this->file);
        $this->total = $this->excel->getTotal();
        $this->data = $this->excel->getData();
        $filePath = $this->excel->getFilePath();

        $halt_top_up = false;
        $this->excelDataFormatError->setAgent($this->agent)->setFile($filePath);
        if ($this->total <= 0) {
            $file_with_errors = $this->excelDataFormatError->uploadFileToCdnAndGetLink();
            unlink($filePath);
            throw new InvalidTopupData($file_with_errors, 'Check The Excel Data Format Properly. There may be excel header or column missing.', 420);
        }
        $total_recharge_amount = 0;

        $this->data->each(function ($value, $key) use (&$halt_top_up, &$total_recharge_amount) {

            $excel_error = $this->hasErrorOnColumn($value);

            if (is_null($excel_error)) {
                $total_recharge_amount += $value->{TopUpExcel::AMOUNT_COLUMN_TITLE};
            } else {
                $this->excelDataFormatError->setRow($key + 2)->updateExcel($excel_error);
                $halt_top_up = true;
            }
        });

        if ($halt_top_up) {
            $file_with_errors = $this->excelDataFormatError->uploadFileToCdnAndGetLink();
            unlink($filePath);
            throw new InvalidTopupData($file_with_errors, 'Check The Excel Data Format Properly.', 420);
        }

        $agent_wallet = floatval($this->agent->wallet);
        if ($total_recharge_amount > $agent_wallet) {
            unlink($filePath);
            throw new InvalidTotalAmount($total_recharge_amount, $agent_wallet, 'You do not have sufficient balance to recharge.', 403);
        }

        $this->bulkExcelCdnFilePath = $this->excel->saveTopupFileToCDN();

        unlink($filePath);

        return parent::check();
    }

    /**
     * @param $value
     * @return string|null
     * @throws Exception
     */
    private function hasErrorOnColumn($value): ?string
    {
        $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
        $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;
        $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
        $connection_type = TopUpExcel::TYPE_COLUMN_TITLE;

        $is_mobile_invalid = !$this->isMobileNumberValid($value->$mobile_field);
        $is_amount_invalid = !$this->isAmountInteger($value->$amount_field);

        if ($is_mobile_invalid && $is_amount_invalid) {
            return 'Mobile number Invalid, Amount Should be Integer';
        } elseif ($is_mobile_invalid) {
            return 'Mobile number Invalid';
        } elseif ($is_amount_invalid) {
            return 'Amount Should be Integer';
        } elseif ($this->isOtfNumberBlockedForBusiness() && $this->isAmountBlocked($value->$operator_field, $value->$connection_type, $value->$amount_field)) {
            return 'The recharge amount is blocked due to OTF activation issue';
        } elseif ($this->isPrepaidAmountLimitExceedForBusiness($amount_field, $value, $connection_type)) {
            return 'The amount exceeded your topUp prepaid limit';
        } else {
           return null;
        }
    }

    /**
     * @param $mobile
     * @return bool
     */
    private function isMobileNumberValid($mobile): bool
    {
        return BangladeshiMobileValidator::validate(BDMobileFormatter::format($mobile));
    }

    /**
     * @param $amount
     * @return bool
     */
    private function isAmountInteger($amount): bool
    {
        return preg_match('/^\d+$/', $amount);
    }

    /**
     * @return bool
     */
    private function isOtfNumberBlockedForBusiness(): bool
    {
        return $this->agent instanceof Business && $this->request->has('is_otf_allow') && !($this->request->is_otf_allow);
    }

    /**
     * @param $operator
     * @param $connection_type
     * @param $amount
     * @return bool
     * @throws Exception
     */
    public function isAmountBlocked($operator, $connection_type, $amount) : bool
    {
        $this->otfAmountCheck = app(OtfAmountCheck::class);
        $this->otfAmountCheck->setAmount($amount)
            ->setVendor($operator)
            ->setType($connection_type)
            ->setAgent($this->agent);

        return $this->otfAmountCheck->isAmountInOtf();
    }

    /**
     * @param string $amount_field
     * @param $value
     * @param string $connection_type
     * @return bool
     */
    private function isPrepaidAmountLimitExceedForBusiness(string $amount_field, $value, string $connection_type): bool
    {
        return $this->agent instanceof Business && $this->isPrepaidAmountLimitExceed($this->agent, $value->$amount_field, $value->$connection_type);
    }

    /**
     * @param Business $business
     * @param $amount
     * @param $connection_type
     * @return bool
     */
    private function isPrepaidAmountLimitExceed(Business $business, $amount, $connection_type): bool
    {
        if ($connection_type == ConnectionType::PREPAID && ($amount > $business->topup_prepaid_max_limit)) return true;
        return false;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return string
     */
    public function getBulkExcelCdnFilePath(): string
    {
        return $this->bulkExcelCdnFilePath;
    }
}
