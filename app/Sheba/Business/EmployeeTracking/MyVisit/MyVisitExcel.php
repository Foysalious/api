<?php namespace Sheba\Business\EmployeeTracking\MyVisit;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MyVisitExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $myVisitData;

    public function __construct(array $my_visit_data)
    {
        $this->myVisitData = $my_visit_data;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->myVisitData as $visit) {
            $data->push([
                'schedule_date' => $visit['schedule_date'],
                'title' => $visit['title'],
                'description' => $visit['description'],
                'status' => $visit['status'],
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'Visit Date', 'Title', 'Description', 'Status',
        ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}