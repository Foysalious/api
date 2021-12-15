<?php namespace Sheba\Business\Leave\Request;

use Excel as LeaveRequestExcel;

class Excel
{
    /**
     * @var array
     */
    private $leaveRequests;
    private $data = [];

    /**
     * @param array $leaves
     * @return $this
     */
    public function setLeave(array $leaves)
    {
        $this->leaveRequests = $leaves;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        $file_name = 'Leave_request_report';
        LeaveRequestExcel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:G1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(['horizontal' => 'left']);
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->leaveRequests as $leave_request) {
            array_push($this->data, [
                'employee_id' => $leave_request['leave']['employee_id'] ?: 'N/A', 'name' => $leave_request['leave']['name'], 'dept' => $leave_request['leave']['department'], 'type' => $leave_request['leave']['type'], 'total_days' => $leave_request['leave']['total_days'], 'leave_date' => $leave_request['leave']['leave_date'], 'status' => $leave_request['leave']['status'],
            ]);
        }

    }

    private function getHeaders()
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Leave Type', 'Total Leave Days', 'Leave Date(s)', 'Leave Status'];
    }

}
