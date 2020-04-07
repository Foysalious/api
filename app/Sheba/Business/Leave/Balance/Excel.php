<?php namespace Sheba\Business\Leave\Balance;

use Sheba\Reports\ExcelHandler;

class Excel
{
    private $balanceData;
    private $leave_types;
    private $excelHandler;
    private $data;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
    }

    public function setBalanceData(array $leave_balances, array $leave_types)
    {
        $this->balanceData = $leave_balances;
        $this->leave_types = $leave_types;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        dd($this->data);
        return $this->excelHandler->setName('Leave Balance Report')->createReport($this->data)->download();
    }

    private function makeData()
    {
        foreach ($this->balanceData as $balance) {
            $count = 0;
            $balance_array = [];
            foreach ($this->leave_types as $leave_type)
            {
                array_push($balance_array, [
                    $leave_type['title'] => $balance['leave_balance'][$count]['used_leaves']. ' / ' .$balance['leave_balance'][$count]['allowed_leaves']
                ]);
                $count++;
            }
            array_push($this->data, [
                'employee_name' => $balance['employee_name'],
                $balance_array
            ]);
        }
    }
}