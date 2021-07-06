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
    protected $oldStock;
    protected $oldCost;
    protected $isUpdate = false;

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


    /**
     * @param mixed $oldStock
     * @return ExpenseEntry
     */
    public function setOldStock($oldStock)
    {
        $this->oldStock = $oldStock;
        return $this;
    }

    /**
     * @param mixed $oldCost
     * @return ExpenseEntry
     */
    public function setOldCost($oldCost)
    {
        $this->oldCost = $oldCost;
        return $this;
    }


    /**
     * @param bool $isUpdate
     * @return ExpenseEntry
     */
    public function setIsUpdate(bool $isUpdate): ExpenseEntry
    {
        $this->isUpdate = $isUpdate;
        return $this;
    }

    public function create()
    {
        if($this->isUpdate) {
            $negativeEntryData = $this->makeNegativeEntryData();
            $this->accountingRepo->storeEntry($negativeEntryData, EntryTypes::INVENTORY);
        }
        $data = $this->makeData();
        $this->accountingRepo->storeEntry($data, EntryTypes::INVENTORY);
    }

    private function makeData()
    {
        $data = collect();
        $data->partner              = $this->partner;
        $data->amount               = $this->stock * $this->costPerUnit;
        $data->from_account_key     = $this->accountingInfo['from_account'];
        $data->to_account_key       = $this->id;
        $data->customer_id          = $this->accountingInfo['supplier_id'] ?? null;
        $data->inventory_products   = json_encode([['id' => $this->id, 'unit_price' => $this->costPerUnit, 'name' => $this->name, 'quantity' => $this->stock]]);
        $data->amount_cleared       = $this->accountingInfo['transaction_type'] == 'due' ?  $this->accountingInfo['amount_cleared'] : $this->stock * $this->costPerUnit;
        $data->source_id            = null;
        return $data;
    }

    private function makeNegativeEntryData()
    {
        $data = collect();
        $data->partner              = $this->partner;
        $data->amount               = $this->oldStock * $this->oldCost;
        $data->from_account_key     = $this->id;
        $data->to_account_key       = $this->accountingInfo['from_account'];
        $data->customer_id          = $this->accountingInfo['supplier_id'] ?? null;
        $data->inventory_products   = json_encode([['id' => $this->id, 'unit_price' => $this->oldCost, 'name' => $this->name, 'quantity' => $this->oldStock]]);
        $data->amount_cleared       = $this->oldStock * $this->oldCost;
        $data->source_id            = null;
        return $data;
    }

}
