<?php namespace App\Sheba\Pos\Product\Accounting;


use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;

class ExpenseEntry
{

    protected $name;
    protected $id;
    protected $stock;
    protected $costPerUnit;
    protected $accountingInfo;
    /**
     * @var AccountingRepository
     */
    private $accountingRepository;
    protected $partner;

    public function __construct(AccountingRepository $accountingRepo) {
        $this->accountingRepo = $accountingRepo;
    }
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

    public function setPartner($partner)
    {
        $this->partner = $partner;
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
        $data = $this->makeData();
        $this->accountingRepo->storeEntry($data, EntryTypes::INVENTORY);
    }

    private function makeData()
    {
        $data = collect();
        $data->partner = $this->partner;
        $data->amount = $this->stock * $this->costPerUnit;
        $data->from_account_key = $this->accountingInfo['from_account'];
        $data->to_account_key = $this->id;
        $data->customer_id = $this->accountingInfo['supplier_id'];
        $data->inventory_products = [['id' => $this->id, 'unit_price' => $this->costPerUnit, 'name' => $this->name, 'quantity' => $this->stock]];
        if ($this->accountingInfo['transaction_type'] == 'due')
            $data->amount_cleared = $this->accountingInfo['amount_cleared'];
        $data->source_id = null;
        return $data;
    }

}