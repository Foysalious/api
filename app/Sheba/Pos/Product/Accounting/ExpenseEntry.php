<?php namespace App\Sheba\Pos\Product\Accounting;


class ExpenseEntry
{

    protected $name;
    protected $id;
    protected $stock;
    protected $costPerUnit;
    protected $accountingInfo;

    /**
     * @param mixed $stock
     * @return ExpenseEntry
     */
    public function setNewStock($stock)
    {
        $this->stock = $stock;
        return $this;
    }

    /**
     * @param mixed $costPerUnit
     * @return ExpenseEntry
     */
    public function setCostPerUnit($costPerUnit)
    {
        $this->costPerUnit = $costPerUnit;
        return $this;
    }

    /**
     * @param mixed $accountingInfo
     * @return ExpenseEntry
     */
    public function setAccountingInfo($accountingInfo)
    {
        $this->accountingInfo = $accountingInfo;
        return $this;
    }

    /**
     * @param mixed $name
     * @return ExpenseEntry
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $id
     * @return ExpenseEntry
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function create()
    {
        $this->makeData();

    }

    private function makeData()
    {

        $data = [
            'amount' => $this->stock * $this->costPerUnit,
            'from_account_key' => $this->accountingInfo['from_account'],
            'to_account_key' => $this->id,
            'customer_id' => $this->accountingInfo['supplier_id'],
            'inventory_products' => [
                [
                    'id' => $this->id,
                    'unit_price' => $this->costPerUnit,
                    'name' => $this->name,
                    'quantity' => $this->stock
                ]
            ]
        ];

        if($this->accountingInfo['transaction_type'] == 'due')
            $data['amount_cleared'] =  $this->accountingInfo['amount_cleared'];

        return $data;
    }

}