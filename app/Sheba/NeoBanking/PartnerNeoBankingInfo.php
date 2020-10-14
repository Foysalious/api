<?php


namespace Sheba\NeoBanking;


use App\Models\Partner;

class PartnerNeoBankingInfo
{
    /** @var Partner $partner */
    protected $partner;
    protected $is_gigatech_verified;
    protected $information_form_bank_account = [];
    protected $data;

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        $this->data    = $this->partner->neoBankInfo;
        return $this;
    }

    public function personal() { }

    public function institution() { }

    public function account() { }

    public function documents() { }

    public function nid_selfie() { }

    public function nominee() { }

    public function getByCode() { }
}
