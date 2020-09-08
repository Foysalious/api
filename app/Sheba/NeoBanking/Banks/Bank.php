<?php


namespace Sheba\NeoBanking\Banks;


use Sheba\NeoBanking\DTO\BankFormCategory;

abstract class Bank
{
    protected $id;
    protected $name;
    protected $logo;
    protected $name_bn;

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    abstract public function categories();

    abstract public function accountInfo(): array;

    abstract public function categoryDetails(BankFormCategory $category): array;
}
