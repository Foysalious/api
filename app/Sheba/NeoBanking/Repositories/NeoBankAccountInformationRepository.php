<?php


namespace Sheba\NeoBanking\Repositories;

use Sheba\Dal\PartnerNeoBankingInfo\Model as PartnerNeoBankInfo;
use App\Models\Partner;
use Sheba\NeoBanking\BankInformation;

class NeoBankAccountInformationRepository extends PartnerNeoBankInfo
{
    /** @var Partner $partner */
    private $partner;

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param Partner $partner
     * @return NeoBankAccountInformationRepository
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function getByPartner()
    {
        return self::where('partner_id', $this->partner->id)->first();
    }

    public function getAccountInformation(): BankInformation
    {
        $data = $this->getByPartner();
        if (!empty($data)) return (new BankInformation(json_decode($data->information_for_bank_account)));
        return (new BankInformation([]));
    }
}
