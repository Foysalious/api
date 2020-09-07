<?php


namespace Sheba\NeoBanking\Banks;


use Sheba\NeoBanking\DTO\BankFormCategory;

abstract class Bank
{
    abstract public function categories();
    abstract public function accountInfo(): array;
    abstract public function categoryDetails(BankFormCategory $category): array;
}
