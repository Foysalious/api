<?php namespace Sheba\TopUp\Jobs;

use App\Models\TopUpOrder;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Excel;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\Dal\TopUpBulkRequest\Statuses;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\Sms\Sms;
use Sheba\TopUp\TopUpExcel;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;

class TopUpExcelJob extends TopUpJob
{
    use FileManager, CdnFileManager, ModificationFields;

    private $file;
    private $row;
    private $totalRow;
    /** @var LaravelExcelReader */
    private $excel = null;
    /** @var TopUpBulkRequest */
    private $bulk;

    /**
     * TopUpExcelJob constructor.
     * @param $agent
     * @param TopUpOrder $topup_order
     * @param $row
     * @param $total_row
     * @param TopUpBulkRequest $bulk
     */
    public function __construct($agent, TopUpOrder $topup_order, $row, $total_row, TopUpBulkRequest $bulk)
    {
        $this->setModifier($agent);
        parent::__construct($topup_order);

        $this->row = $row;
        $this->totalRow = $total_row;
        $this->bulk = $bulk;
    }

    /**
     * @throws Exception
     */
    protected function takeSuccessfulAction()
    {
        $this->updateExcel('Successful');
        $this->takeCompletedAction();
    }

    /**
     * @throws Exception
     */
    protected function takeUnsuccessfulAction()
    {
        $this->updateExcel('Failed', $this->topUp->getError()->errorMessage);
        $this->takeCompletedAction();
    }

    private function getExcel(): LaravelExcelReader
    {
        $this->file = $this->getFile($this->bulk);
        if (!$this->excel) $this->excel = Excel::selectSheets(TopUpExcel::SHEET)->load($this->file);
        return $this->excel;
    }

    private function updateExcel($status, $message = null)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::STATUS_COLUMN . $this->row, $status);
        if ($message) {
            $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::MESSAGE_COLUMN . $this->row, $message);
        }
        $this->excel->save();
    }

    private function takeCompletedAction()
    {
        if ($this->row == $this->totalRow + 1) {
            $name = strtolower(class_basename($this->agent)) . '_' . $this->agent->id;
            $file_name = $this->uniqueFileName($this->file, $name, $this->getExcel()->ext);
            $file_path = $this->saveFileToCDN($this->file, getBulkTopUpFolder(), $file_name);
            unlink($this->file);

            $this->updateBulkTopUpStatus(Statuses::COMPLETED, $file_path);

            $msg = "Your top up request has been processed. You can find the results here: " . $file_path;

            (new Sms())
                ->setFeatureType(FeatureType::TOP_UP)
                ->setBusinessType(BusinessType::BONDHU)
                ->shoot($this->agent->getMobile(), $msg);
        }
    }

    /**
     * @param $status
     * @param $file
     */
    public function updateBulkTopUpStatus($status, $file)
    {
        $this->bulk->status = $status;
        $this->bulk->file = $file;
        $this->withUpdateModificationField($this->bulk);
        $this->bulk->save();
    }

    /**
     * @param TopUpBulkRequest $bulk
     * @return string
     */
    private function getFile(TopUpBulkRequest $bulk): string
    {
        $file_name = basename($bulk->file);
        $file_name_with_folder = getStorageExportFolder() . $file_name;
        if (!File::exists($file_name_with_folder))
            File::put(getStorageExportFolder() . $file_name, file_get_contents($bulk->file));

        return $file_name_with_folder;
    }
}
