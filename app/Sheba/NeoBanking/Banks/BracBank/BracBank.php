<?php


namespace Sheba\NeoBanking\Banks;


use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use ReflectionClass;
use ReflectionException;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\BankFormCategoryList;

class BracBank  extends Bank
{
    protected $id      = 2;
    protected $name    = "Brac Bank";
    protected $name_bn = "ব্র্যাক ব্যাংক";

    public function categories():BankFormCategoryList
    {
        // TODO: Implement categories() method.
    }

    public function accountInfo(): BankAccountInfo
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

    public function completion(): BankCompletion
    {
        // TODO: Implement completion() method.
    }

    public function accountDetailInfo(): BankAccountInfoWithTransaction
    {
        // TODO: Implement accountDetailInfo() method.
    }
}
