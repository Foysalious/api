<?php namespace Sheba\Business\CoWorker;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CoworkerExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithColumnFormatting
{
    /** @var array */
    private $employees;

    public function __construct(array $employees)
    {
        $this->employees = $employees;
    }
    
    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->employees as $employee) {
            $data->push([
                'employee_id' => $employee['employee_id'] ?: 'N/A',
                'employee_name' => $employee['profile']['name'],
                'phone' => $employee['profile']['mobile'] ?: '-',
                'email' => $employee['profile']['email'] ?: '-',
                'status' => ucfirst($employee['status']) ?: '-',
                'department' => $employee['department'] ?: '-',
                'designation' => $employee['designation'] ?: '-',
                "manager_name" => $employee['manager_name'] ?: '-',
                "join_date" => $employee['join_date'] ?: '-',
                "employee_grade" => $employee['employee_grade'] ?: '-',
                "employee_type" => $employee['employee_type'] ?: '-',
                "previous_institution" => $employee['previous_institution'] ?: '-',
                "date_of_birth" => $employee['date_of_birth'] ?: '-',
                "address" => $employee['address'] ?: '-',
                "nationality" => $employee['nationality'] ?: '-',
                "nid_no" => $employee['nid_no'] ?: '-',
                "tin_no" => $employee['tin_no'] ?: '-',
                "bank_name" => $employee['bank_name'] ?: '-',
                "bank_account_no" => $employee['bank_account_no'] ?: '-',
                "emergency_contract_person_number" => $employee['emergency_contract_person_number'] ?: '-',
                "emergency_contract_person_name" => $employee['emergency_contract_person_name'] ?: '-',
                "emergency_contract_person_relationship" => $employee['emergency_contract_person_relationship'] ?: '-',
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Employee Name', 'Phone', 'Email', 'Status',
            'Department', 'Designation', 'Manager', 'Joining Date', 'Employee Grade',
            'Employee Type', 'Previous Institution', 'DOB', 'Address',
            'Nationality', 'NID/Passport', 'TIN', 'Bank Name',
            'Bank Account No.','Emergency Contact', 'Name Emergency Contact', 'Relationship Emergency Contact'
        ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('C2');
        $sheet->getStyle('A1:V1')->getFont()->setBold(true);

        $this->collection()->each(function ($item, $key) use (&$sheet) {
            if ($item['status'] != 'Invited' && $item['status'] != 'Inactive') return;

            $row = $key + 2;
            $av_color = (new Color())->setRGB($item['status'] == 'Invited' ? "FF9900" : "FF0000");
            $ad_color = $fv_color = (new Color())->setRGB('060101');

            $sheet->getStyle("A$row:V$row")->getFont()->setColor($av_color);
            $sheet->getStyle("A$row:D$row")->getFont()->setColor($ad_color);
            $sheet->getStyle("F$row:V$row")->getFont()->setColor($fv_color);
        });

        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    public function columnFormats(): array
    {
        return [
            'C' => '#0',
            'P' => '#0',
            'Q' => '#0',
            'S' => '#0',
            'T' => '#0'
        ];
    }

    public function title(): string
    {
        return 'Sheet 1';
    }
}
