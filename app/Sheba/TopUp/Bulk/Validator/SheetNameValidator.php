<?php namespace Sheba\TopUp\Bulk\Validator;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Reader;
use Sheba\TopUp\Bulk\Exception\InvalidSheetName;
use Sheba\TopUp\TopUpExcel;

class SheetNameValidator extends Validator
{
    /**
     * @return bool
     * @throws InvalidSheetName
     */
    public function check(): bool
    {
        $reader = (new Reader())->load($this->file->getRealPath());

        if (!$reader->sheetNameExists(TopUpExcel::SHEET)) throw new InvalidSheetName();

        return parent::check();
    }
}
