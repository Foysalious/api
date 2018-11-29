<?php namespace Sheba\TopUp;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;

class TopUpExcelJob extends TopUpJob
{
    use FileManager, CdnFileManager;

    /** @var LaravelExcelReader */
    private $excel;

    private $row;
    private $totalRow;

    public function __construct($agent, $vendor, TopUpRequest $top_up_request, LaravelExcelReader $excel, $row, $total_row)
    {
        parent::__construct($agent, $vendor, $top_up_request);
        $this->excel = $excel;
        $this->row = $row;
        $this->totalRow = $total_row;
    }

    /**
     * @param TopUp $top_up
     * @throws \Exception
     */
    protected function takeUnsuccessfulAction(TopUp $top_up)
    {
        dd($top_up->getError());
        $this->updateExcel('Failed', $top_up->getError()->errorMessage);
        $this->takeCompletedAction();
    }

    /**
     * @param TopUp $top_up
     * @throws \Exception
     */
    protected function takeSuccessfulAction(TopUp $top_up)
    {
        $this->updateExcel('Successful');
        $this->takeCompletedAction();
    }

    private function updateExcel($status, $message = null)
    {
        $this->excel->getActiveSheet()->setCellValue('E' . $this->row, $status);
        if($message) {
            $this->excel->getActiveSheet()->setCellValue('F' . $this->row, $message);
        }
    }

    private function takeCompletedAction()
    {
        if($this->row == $this->totalRow + 1) {
            $file = $this->excel->store('xls');
            dd($file);
            $file_name = $this->uniqueFileName($file, strtolower(class_basename($this->agent)) . '_' . $this->agent->id);
            $file_path = $this->saveFileToCDN($file, getBulkTopUpFolder(), $file_name);
            dd("notify agent $file_path");
        }
    }
}