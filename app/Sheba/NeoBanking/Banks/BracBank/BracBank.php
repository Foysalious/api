<?php


namespace Sheba\NeoBanking\Banks;


use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use App\Sheba\NeoBanking\Banks\NidInformation;
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
        return new BankFormCategoryList();
    }

    public function accountInfo(): BankAccountInfo
    {
        return new BankAccountInfo();
    }

    public function categoryDetails(BankFormCategory $category): CategoryGetter
    {
       return $category->get();
    }

    public function homeInfo(): array
    {
        return [];
    }

    public function completion(): BankCompletion
    {
        return new BankCompletion();
    }

    public function accountDetailInfo(): BankAccountInfoWithTransaction
    {
        return new BankAccountInfoWithTransaction();
    }

    public function getNidInfo($data): NidInformation
    {
        return new NidInformation();
    }

    public function getSDKLivelinessToken()
    {
        return null;
    }
}
