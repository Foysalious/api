<?php namespace Sheba\Business\Leave\Request;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveRequestExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /** @var array */
    private $leaveRequests;

    /**
     * @param array $leaves
     */
    public function __construct(array $leaves)
    {
        $this->leaveRequests = $leaves;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->leaveRequests as $leave_request) {
            $data->push([
                'employee_id' => $leave_request['leave']['employee_id'] ?: 'N/A',
                'name' => $leave_request['leave']['name'],
                'dept' => $leave_request['leave']['department'],
                'type' => $leave_request['leave']['type'],
                'total_days' => $leave_request['leave']['total_days'],
                'leave_date' => $leave_request['leave']['leave_date'],
                'status' => $leave_request['leave']['status'],
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Leave Type', 'Total Leave Days', 'Leave Date(s)', 'Leave Status'];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

}
