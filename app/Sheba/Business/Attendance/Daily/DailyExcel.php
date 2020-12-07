<?php namespace Sheba\Business\Attendance\Daily;

use Carbon\Carbon;
use Excel;

class DailyExcel
{
    private $dailyData;
    private $data = [];

    public function setData(array $daily_data)
    {
        $this->dailyData = $daily_data;
        return $this;
    }

    public function download()
    {
        $this->makeData();
        $file_name = Carbon::now()->timestamp . '_' . 'daily_attendance_report';
        Excel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:N1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->dailyData as $attendance) {
            array_push($this->data, [
                'date' => "one",
                'employee_id' => "one// Set auto size for sheetone// Set auto size for sheet",
                'employee_name' => "one",
                'department' => "one",
                'status' => "one",
                'check_in_time' => "one",
                'check_in_status' => "one",
                'check_in_location' => "one",
                'check_in_address' => "one",
                'check_out_time' => "one",
                'check_out_status' => "one",
                'check_out_location' => "one",
                'check_out_address' => "one",
                'total_hours' => "one",
            ]);
        }
    }

    private function getHeaders()
    {
        return ['Date', 'Employee ID', 'Employee Name', 'Department',
            'Status', 'Check in time', 'Check in status', 'Check in location',
            'Check in address', 'Check out time', 'Check out status',
            'Check out location', 'Check out address', 'Total Hours'];
    }
}
