<?php namespace App\Sheba\Business\Payslip\TaxHistory;

use Excel as TaxHistory;

class TaxHistoryExcel
{
    private $taxHistoryData;
    private $data = [];

    public function setTaxHistoryData(array $tax_history_data)
    {
        $this->taxHistoryData = $tax_history_data;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        TaxHistory::create('Tax_History', function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:R1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->freezePane('D2');
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->taxHistoryData as $tax_history) {
            array_push($this->data, [
                'employee_id' => $tax_history['employee_id'],
                'employee_name' => $tax_history['employee_name'],
                'department' => $tax_history['department'],
                'basic_salary' => $tax_history['basic_salary'],
                'house_rent' => $tax_history['house_rent'],
                'conveyance' => $tax_history['conveyance'],
                'medical_allowance' => $tax_history['medical_allowance'],
                'others' => $tax_history['others'],
                'total_taxable_income' => $tax_history['total_taxable_income'],
                'exemption_amount' => $tax_history['exemption_amount'],
                'remaining_taxable_income' => $tax_history['remaining_taxable_income'],
                '5_percent_slab' => $tax_history['5_percent_slab'],
                '10_percent_slab' => $tax_history['10_percent_slab'],
                '15_percent_slab' => $tax_history['15_percent_slab'],
                '20_percent_slab' => $tax_history['20_percent_slab'],
                '25_percent_slab' => $tax_history['25_percent_slab'],
                'total_tax_amount_yearly' => $tax_history['total_tax_amount_yearly'],
                'total_tax_amount_monthly' => $tax_history['total_tax_amount_monthly'],
            ]);
        }
    }

    private function getHeaders()
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Basic', 'House Rent', 'Conveyance', 'Medical', 'Others', 'Total Taxable Income', 'Exemption Amount', 'Remaining Taxable Income', '5 % slab', '10 % slab', '15 % slab', '20 % slab', '25 % slab', 'Total Tax Amount(Yearly)', 'Total Tax Amount(Monthly)'];


    }

}