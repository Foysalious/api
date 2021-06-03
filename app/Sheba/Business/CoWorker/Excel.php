<?php namespace Sheba\Business\CoWorker;

use Excel as EmployeeExcel;
use PHPExcel_Style_Fill;

class Excel
{
    /**
     * @var array
     */
    private $employees;
    private $data = [];

    /**
     * @param array $employees
     * @return $this
     */
    public function setEmployee(array $employees)
    {
        $this->employees = $employees;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        $file_name = 'Coworker Report';
        EmployeeExcel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRowAndColumn();
                $sheet->cell('A1:V1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(['horizontal' => 'left']);
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }
    
    private function makeData()
    {
        foreach ($this->employees as $employee) {
            array_push($this->data, [
                'employee_id' => $employee['employee_id'] ?: 'N/A',
                'employee_name' => $employee['employee_name'],
                'phone' => $employee['phone'] ?: '-',
                'email' => $employee['email'] ?: '-',
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
                "emergency_contract_person_name" => $employee['emergency_contract_person_name'] ?: '-',
                "emergency_contract_person_number" => $employee['emergency_contract_person_number'] ?: '-',
                "emergency_contract_person_relationship" => $employee['emergency_contract_person_relationship'] ?: '-',
            ]);
        }

    }

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            'Employee ID',
            'Employee Name',
            'Phone',
            'Email',
            'Status',
            'Department',
            'Designation',
            'Manager',
            'Employee Grade',
            'Joining Date',
            'Employee Type',
            'Previous Institution',
            'DOB',
            'Address',
            'Nationality',
            'NID/Passport',
            'TIN',
            'Bank Name',
            'Bank Account No.',
            'Emergency Contact',
            'Name Emergency Contact',
            'Relationship Emergency Contact'
        ];
    }

}
