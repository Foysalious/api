<?php namespace Sheba\Subscription\Partner\Access\RulesDescriber;

/**
 * @property INVOICE $INVOICE
 * @property Due $DUE
 * @property Inventory $INVENTORY
 */
class Pos extends BaseRule
{
    protected $INVOICE;
    protected $DUE;
    protected $INVENTORY;

    public function __construct()
    {
        $this->INVOICE = new Invoice();
        $this->DUE = new Due();
        $this->INVENTORY = new Inventory();
    }

    protected function register($name, $prefix)
    {
        if ($name == "INVOICE") return $this->INVOICE->setPrefix($prefix, 'invoice');
        elseif ($name == "DUE") return $this->DUE->setPrefix($prefix, 'due');
        elseif ($name == "INVENTORY") return $this->INVENTORY->setPrefix($prefix, 'inventory');
    }
}