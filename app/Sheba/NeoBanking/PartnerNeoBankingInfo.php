<?php


namespace Sheba\NeoBanking;


use App\Models\Partner;
use Carbon\Carbon;

class PartnerNeoBankingInfo
{
    /** @var Partner $partner */
    protected $partner;
    protected $is_gigatech_verified;
    protected $information_for_bank_account = [];
    protected $data;

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        $this->data    = $this->partner->neoBankInfo;
        if (!empty($this->data) && !empty($this->data->information_for_bank_account)){
            $this->information_for_bank_account = (array)json_decode($this->data->information_for_bank_account,0);
        }
        return $this;
    }

    public function personal()
    {
        if (!empty($this->information_for_bank_account) && isset($this->information_for_bank_account['personal'])) return $this->information_for_bank_account['personal'];
        return [];
    }

    public function institution()
    {
        if (!empty($this->information_for_bank_account) && isset($this->information_for_bank_account['institution'])) return $this->information_for_bank_account['institution'];
        return ["mobile" => $this->partner->getManagerMobile()];
    }

    public function account()
    {
        if (!empty($this->information_for_bank_account) && isset($this->information_for_bank_account['account'])) return $this->information_for_bank_account['account'];
        return [];
    }

    public function documents()
    {
        if (!empty($this->information_for_bank_account) && isset($this->information_for_bank_account['documents'])) return $this->information_for_bank_account['documents'];
        return [];
    }

    public function nid_selfie()
    {
        if (!empty($this->information_for_bank_account) && isset($this->information_for_bank_account['nid_selfie'])) return $this->information_for_bank_account['nid_selfie'];
        return [];
    }

    public function nominee()
    {
        if (!empty($this->information_for_bank_account) && isset($this->information_for_bank_account['nominee'])) return $this->information_for_bank_account['nominee'];
        return [];
    }

    public function getByCode($code)
    {
        return $this->$code();
    }

    public function getData()
    {
        return $this->data;
    }

    public function postByCode($code, $data)
    {
        $data['updated_at']                         = Carbon::now()->format('Y-m-d H:s:i');
        if($code == 'personal' && isset($data['birth_date'])) $data['birth_date'] = Carbon::parse($data['birth_date'])->format('d-m-Y');
        if(isset($data['nominee_birth_date'])) $data['nominee_birth_date'] = Carbon::parse($data['nominee_birth_date'])->format('d-m-Y');
        $this->information_for_bank_account[$code]  = $data;
        return $this->partner->neoBankInfo ? $this->partner->neoBankInfo->update(['information_for_bank_account' => json_encode($this->information_for_bank_account)]) : $this->partner->neoBankInfo()->create(['information_for_bank_account' => json_encode($this->information_for_bank_account), 'partner_id' => $this->partner->id, 'is_gigatech_verified' => 0]);
    }
}
