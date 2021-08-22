<?php


namespace Sheba\NeoBanking;


use App\Models\Partner;
use Carbon\Carbon;
use Sheba\NeoBanking\Exceptions\CategoryPostDataInvalidException;

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
        if (isset($this->information_for_bank_account['nid_selfie'])){
            $nidSelfie = $this->information_for_bank_account['nid_selfie'];
            return [
                'applicant_name' => $nidSelfie->applicant_name_eng,
                'birth_date' =>  Carbon::parse($nidSelfie->dob)->format('d-m-Y'),
                'father_name' => $nidSelfie->father_name,
                'mother_name' => $nidSelfie->mother_name,
                'nid_passport_birth_cer_number' => $nidSelfie->nid_no,
                'company_name' => $this->isValidString() ? strtoupper($this->partner->name) : '',
            ];
        }
        return [
            'company_name' => $this->isValidString() ? strtoupper($this->partner->name) : '',
        ];
    }

    public function institution()
    {
        if (!empty($this->information_for_bank_account) && isset($this->information_for_bank_account['institution'])) return $this->information_for_bank_account['institution'];
        return [
            "mobile"       => $this->partner->getManagerMobile(),
            'company_name' => $this->isValidString() ? strtoupper($this->partner->name) : '',
        ];
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
        if ($code=== 'personal')
        {
            if (!isset($this->information_for_bank_account['nid_selfie'])) throw new CategoryPostDataInvalidException('You need to provide nid first');

            $data = $this->setNidData($data);
        }
        $data['updated_at'] = Carbon::now()->format('Y-m-d H:s:i');
        if(isset($data['nominee_birth_date'])) $data['nominee_birth_date'] = Carbon::parse($data['nominee_birth_date'])->format('d-m-Y');
        $this->information_for_bank_account[$code]  = $data;
        if ($this->partner->neoBankInfo)
        {
            return $this->partner->neoBankInfo->update(['information_for_bank_account' => json_encode($this->information_for_bank_account)]);
        }
        return $this->partner
            ->neoBankInfo()
            ->create([
                'information_for_bank_account' => json_encode($this->information_for_bank_account),
                'partner_id' => $this->partner->id,
                'is_gigatech_verified' => 0
            ]);
    }

    private function setNidData($data)
    {
        $nidSelfie = $this->information_for_bank_account['nid_selfie'];
        $data['applicant_name'] = $nidSelfie->applicant_name_eng;
        $data['birth_date'] =  Carbon::parse($nidSelfie->dob)->format('d-m-Y');
        $data['father_name'] = $nidSelfie->father_name;
        $data['mother_name'] = $nidSelfie->mother_name;
        $data['nid_passport_birth_cer_number'] = $nidSelfie->nid_no;
        return $data;
    }

    private function isValidString(): bool
    {
        return (!preg_match('/[^A-Za-z0-9. -]/', $this->partner->name));
    }
}
