<?php


namespace Sheba\NeoBanking\Banks;


use App\Models\Partner;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\DTO\BankFormCategoryList;
use Sheba\NeoBanking\Exceptions\InvalidBankCode;
use Sheba\NeoBanking\Exceptions\InvalidBankFormCategoryCode;
use Sheba\NeoBanking\Exceptions\InvalidListInsertion;
use Sheba\NeoBanking\Statics\BankStatics;

class BankFormCategoryFactory
{
    /** @var Partner $partner */
    private $partner;
    /** @var Bank $bank */
    private $bank;
    private $classPath;

    public function __construct()
    {
        $this->classPath = "Sheba\\NeoBanking\\Banks\\Categories";
    }

    /**
     * @param Partner $partner
     * @return BankFormCategoryFactory
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param Bank $bank
     * @return BankFormCategoryFactory
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    /**
     * @return BankFormCategoryList
     * @throws InvalidBankCode
     * @throws InvalidListInsertion
     */
    public function getAllCategory(): BankFormCategoryList
    {
        $categoryList = BankStatics::BankCategoryList($this->bank->code);
        $listData     = new BankFormCategoryList();
        foreach ($categoryList as $key => $cat) {
            /** @var BankFormCategory $cls */
            $cls = app("$this->classPath\\$cat");
            $cls->setPartner($this->partner)->setBank($this->bank);
            $listData->append($cls);
        }
        return $listData;
    }

    /**
     * @param $code
     * @return BankFormCategory
     * @throws InvalidBankCode
     * @throws InvalidBankFormCategoryCode
     */
    public function getCategoryByCode($code): BankFormCategory
    {
        $this->bank->loadInfo();
        $categoryList = BankStatics::BankCategoryList($this->bank->code);
        if (isset($categoryList[$code])) {
            $cat = $categoryList[$code];
            /** @var BankFormCategory $cls */
            $cls = app("$this->classPath\\$cat");
            $cls->setPartner($this->partner)->setBank($this->bank);
            $cls->setBankAccountData($this->bank->getBankInfo());
            return $cls;
        }
        throw new InvalidBankFormCategoryCode();
    }
}
