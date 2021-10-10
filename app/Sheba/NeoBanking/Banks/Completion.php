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
    private $gigatech_data;
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

    public function getAll(): BankCompletion
    {
        $completion = $this->completionPercentage();
        $this->setGigaTechData()->setApply($completion);
        return (new BankCompletion())->setGigaTechStatusInfo($this->gigatech_data)
            ->setCompletion($completion)->setCanApply($this->can_apply)
            ->setBankDetailTitle(BankStatics::AccountDetailsTitle())
            ->setBankDetailLink(BankStatics::AccountDetailsURL())
            ->setPblTermsAndCondition(BankStatics::PblTermsAndCondition())
            ->setPepIpDefinition(BankStatics::PepIpDefinition())
            ->setMessage(BankStatics::completionMessage($this->can_apply))
            ->setMessageType(BankStatics::completionType($this->can_apply));
    }

    public function completionPercentage(): array
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
        return $completion;
    }

    private function setGigaTechData()
    {
        $this->mobile = str_replace('+88', '', $this->mobile);
//        $this->gigatech_data = $this->bank->getGigatechKycStatus(["mobile" => $this->mobile]);
        $this->gigatech_data = [
            "code" => 200,
            "data" => [
                "status" => 'success',
                "data" => [
                    "status" => "passed",
                ],
                "detail" => [
                    "nid_no" => 123122324243131
                ]
            ]
        ];
        return $this;
    }

    public function setApply($completion)
    {
        foreach ($completion as $single)
            if ($single['completion_percentage']['en'] != 100) $this->can_apply = 0;
        if ($this->can_apply === 1) {
            $bank_data = $this->bank->getBankInfo()->getData();
            if (json_decode($bank_data['information_for_bank_account'])->personal->fatca_information->fatca_information_yes) $this->can_apply = 0;
            elseif (!$bank_data->is_gigatech_verified && isset($this->gigatech_data->data->data->status) && $this->gigatech_data->data->data->status !== "passed") $this->can_apply = 0;
            elseif (!isset($this->gigatech_data->data->data->status) && !$bank_data->is_gigatech_verified) $this->can_apply = 0;
        }
    }

}
