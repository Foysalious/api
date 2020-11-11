<?php namespace Sheba\NeoBanking\Banks;


use App\Models\Partner;
use ReflectionException;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Exceptions\InvalidBankCode;
use Sheba\NeoBanking\Exceptions\InvalidListInsertion;
use Sheba\NeoBanking\Statics\BankStatics;

class Completion
{
    /** @var Partner $partner */
    private $partner;
    /** @var Bank $bank */
    private $bank;
    private $mobile;

    /**
     * @param Partner $partner
     * @return Completion
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param Bank $bank
     * @return Completion
     */
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @throws InvalidBankCode
     * @throws InvalidListInsertion|ReflectionException
     */
    public function getAll(): BankCompletion
    {
        $list       = (new BankFormCategoryFactory())->setBank($this->bank)->setPartner($this->partner)->getAllCategory();
        $iterator   = $list->getIterator();
        $completion = [];
        while ($iterator->valid()) {
            /** @var BankFormCategory $current */
            $current      = $iterator->current();
            $completion[] = $current->getCompletionDetails()->toArray();
            $iterator->next();
        }
        return (new BankCompletion())->setGigaTechStatusInfo($this->getGigaTechData())->setCompletion($completion)->setCanApply(1)->setBankDetailTitle(BankStatics::AccountDetailsTitle())->setBankDetailLink(BankStatics::AccountDetailsURL())->setMessage('প্রয়োজনীয় তথ্য দেয়া সম্পন্ন হয়েছ, আপনি ব্যাংক অ্যাকাউন্ট জন্য আবেদন করতে পারবেন।')->setMessageType('info');
    }

    private function getGigaTechData()
    {
        return $this->bank->getGigatechKycStatus(["mobile" => $this->mobile]);
    }

}
