<?php namespace Sheba\TopUp\Bulk\Validator;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Reader;
use Sheba\TopUp\Bulk\Exception\InvalidSheetName;
use Sheba\TopUp\TopUpExcel;

class SheetNameValidator extends Validator
{
    /**
     * @return bool
     * @throws InvalidSheetName
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function check(): bool
    {
        $reader = (new Reader())->load($this->file->getRealPath());

        if (!$reader->sheetNameExists(TopUpExcel::SHEET)) throw new InvalidSheetName();

        if ($reader->getIndex($reader->getSheetByName(TopUpExcel::SHEET)) != TopUpExcel::SHEET_INDEX) throw new InvalidSheetName();

        return parent::check();
    }
}
