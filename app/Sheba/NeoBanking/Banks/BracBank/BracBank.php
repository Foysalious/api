<?php namespace Sheba\NeoBanking\Banks;


use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use App\Sheba\NeoBanking\Banks\NidInformation;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\BankFormCategoryList;

class BracBank  extends Bank
{
    public $id      = 2;
    public $name    = "Brac Bank";
    public $name_bn = "ব্র্যাক ব্যাংক";

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

    public function accountCreate()
    {
        // TODO: Implement accountCreate() method.
    }

    public function storeAccountNumber($account_no)
    {
        // TODO: Implement storeAccountNumber() method.
    }

    public function transactionList()
    {
        // TODO: Implement transactionList() method.
    }

    public function getGigatechKycStatus($data)
    {
        // TODO: Implement getGigatechKycStatus() method.
    }

    public function storeGigatechKyc($data)
    {
        // TODO: Implement storeGigatechKyc() method.
    }
}
