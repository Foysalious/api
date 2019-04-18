<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosService;
use Sheba\Repositories\BaseRepository;

class PosServiceRepository extends BaseRepository
{
    /**
     * @param $data
     * @return PartnerPosService
     */
    public function save($data)
    {
        return PartnerPosService::create($this->withCreateModificationField($data));
    }

    /**
     * @param PartnerPosService $service
     * @param $data
     * @return bool|int
     */
    public function update(PartnerPosService $service, $data)
    {
        return $service->update($this->withUpdateModificationField($data));
    }
}