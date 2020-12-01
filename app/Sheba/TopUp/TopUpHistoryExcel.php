<?php namespace Sheba\TopUp;

use Carbon\Carbon;
use Excel;

class TopUpHistoryExcel
{
    private $suggestions;
    private $topups;
    private $agent;

    public function setData($topup_data)
    {
        $this->suggestions = [['operator', 'connection_type'], ['ROBI', 'Postpaid'], ['GP', 'Prepaid'], ['AIRTEL'], ['BANGLALINK'], ['TELETALK']];
        $this->topups = $topup_data;

        return $this;
    }

    /**
     * @param $agent
     * @return $this
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function takeCompletedAction()
    {
        $file_name = Carbon::now()->timestamp . '_' . $this->agent->id . '_' . strtolower(class_basename($this->agent)) . '_' . 'topup_history_format_file';

        Excel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->topups);
                foreach ($this->topups as $index => $topup) {
                    $row = "B" . ($index + 2);
                    $objValidation = $sheet->getCell($row)->getDataValidation();
                    $objValidation->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST);
                    $objValidation->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list.');
                    $objValidation->setPromptTitle('Pick from list');
                    $objValidation->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation->setFormula1('suggestion!$B$2:$B$6');
                }
            });

            $excel->sheet('suggestion', function ($sheet) {
                $sheet->fromArray($this->suggestions, null, 'B1', false, false);
            });
        })->export('xlsx');
    }
}
