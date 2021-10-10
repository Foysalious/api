<?php namespace Sheba\Business\EmployeeTracking\MyVisit;

use Excel as MyVisitExcel;
use Carbon\Carbon;

class Excel
{
    private $myVisitData;
    private $data = [];

    public function setMyVisitData(array $my_visit_data)
    {
        $this->myVisitData = $my_visit_data;
        return $this;
    }

    public function get()
    {
        $header = $this->getHeaders();
        $this->makeData();
        $file_name = 'My_visit_report_' . Carbon::now()->timestamp;
        MyVisitExcel::create($file_name, function ($excel) use ($header) {
            $excel->sheet('data', function ($sheet) use ($header) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($header);
                $sheet->freezeFirstRow();
                $sheet->cell('A1:F1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->myVisitData as $visit) {
            array_push($this->data, [
                'schedule_date' => $visit['schedule_date'],
                'title' => $visit['title'],
                'description' => $visit['description'],
                'status' => $visit['status'],
            ]);
        }

    }

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            'Visit Date', 'Title', 'Description', 'Status',
        ];
    }
}