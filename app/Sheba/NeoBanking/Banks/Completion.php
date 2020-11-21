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
    private $can_apply = 1;

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
        $this->setCanApply($completion);
        return (new BankCompletion())->setGigaTechStatusInfo($this->getGigaTechData())->setCompletion($completion)->setCanApply($this->can_apply)->setBankDetailTitle(BankStatics::AccountDetailsTitle())->setBankDetailLink(BankStatics::AccountDetailsURL())->setMessage(BankStatics::completionMessage($this->can_apply))->setMessageType(BankStatics::completionType($this->can_apply));
    }

    private function getGigaTechData()
    {
        return $this->bank->getGigatechKycStatus(["mobile" => $this->mobile]);
    }

    public function setCanApply($completion)
    {
        foreach ($completion as $single)
            if($single['completion_percentage']['en'] != 100) $this->can_apply = 0;
    }

}
