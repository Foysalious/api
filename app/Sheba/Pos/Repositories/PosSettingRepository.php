<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosSetting;
use Sheba\Repositories\BaseRepository;

class PosSettingRepository extends BaseRepository
{
    /**
     * @param $data
     * @return PartnerPosSetting
     */
    public function save($data)
    {
        return PartnerPosSetting::create($this->withCreateModificationField($data));
    }
}