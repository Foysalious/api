<?php namespace Sheba\Repositories;

use App\Models\PartnerPosService;

class PosServiceRepository extends BaseRepository
{
    public function save($data)
    {
        return PartnerPosService::create($this->withCreateModificationField($data));
    }
}