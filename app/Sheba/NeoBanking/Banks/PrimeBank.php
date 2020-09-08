<?php


namespace Sheba\NeoBanking\Banks;


use Sheba\NeoBanking\DTO\BankFormCategory;

class PrimeBank extends Bank
{
    public function __construct()
    {
        $this->id      = 1;
        $this->name    = "Prime Bank";
        $this->name_bn = "প্রাইম ব্যাংক";
    }

    public function categories()
    {
        // TODO: Implement categories() method.
    }

    public function accountInfo(): array
    {
        // TODO: Implement accountInfo() method.
    }

    public function categoryDetails(BankFormCategory $category): array
    {
        // TODO: Implement categoryDetails() method.
    }
}
