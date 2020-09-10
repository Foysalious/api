<?php


namespace Sheba\NeoBanking\Banks;


use ReflectionClass;
use ReflectionException;
use Sheba\NeoBanking\DTO\BankFormCategory;

class BracBank extends Bank
{
    protected $id      = 2;
    protected $name    = "Brac Bank";
    protected $name_bn = "ব্র্যাক ব্যাংক";

    public function categories()
    {
        // TODO: Implement categories() method.
    }

    public function accountInfo($id): BankAccountInfo
    {
        return new BankAccountInfo();
    }

    public function categoryDetails(BankFormCategory $category): array
    {
        // TODO: Implement categoryDetails() method.
    }

    public function homeInfo(): array
    {
        // TODO: Implement homeInfo() method.
    }
}
