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
        // TODO: Implement categories() method.
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

    public function getNidInfo($data): NidInformation
    {
        // TODO: Implement getNidInfo() method.
    }

    public function getSDKLivelinessToken()
    {
        // TODO: Implement getSDKLivelinessToken() method.
    }
}
