<?php namespace Sheba\Business\Leave\Balance;

use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;

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

    /**
     * @param array $leave_balances
     * @return $this
     */
    public function setBalance(array $leave_balances)
    {
        $this->balanceData = $leave_balances;
        return $this;
    }

    /**
     * @param array $leave_types
     * @return $this
     */
    public function setLeaveType(array $leave_types)
    {
        $leave_type_array = [];
        foreach ($leave_types as $leave_type) {
            $leave_type_array[$leave_type['title']] = 0;
        }

        $this->leave_types = $leave_type_array;
        return $this;
    }

    /**
     * @return void
     * @throws NotAssociativeArray
     */
    public function get()
    {
        $this->makeData();
        return $this->excelHandler->setName('Leave Balance Report')->createReport($this->data)->download();
    }

    private function makeData()
    {
        foreach ($this->balanceData as $balance) {
            foreach ($balance['leave_balance'] as $leave_type) {
                $this->leave_types[$leave_type['title']] ='"'.$leave_type['used_leaves'] . '/' . $leave_type['allowed_leaves'].'"';
            }
            $data = ['employee_name' => $balance['employee_name']] + $this->leave_types;
            array_push($this->data, $data);
        }
    }
}
