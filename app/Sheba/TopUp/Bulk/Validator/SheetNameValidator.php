<?php namespace Sheba\TopUp\Bulk\Validator;

use Sheba\TopUp\Bulk\Exception\InvalidSheetName;
use Sheba\TopUp\TopUpExcel;
use Excel;

class SheetNameValidator extends Validator
{
    /**
     * @return bool
     * @throws InvalidSheetName
     */
    public function check(): bool
    {
        $sheet_names = Excel::load($this->file)->getSheetNames();
        if (!in_array(TopUpExcel::SHEET, $sheet_names))
            throw new InvalidSheetName();

        return parent::check();
    }
}
