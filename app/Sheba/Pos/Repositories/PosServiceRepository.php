<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosService;
use Exception;
use Sheba\Repositories\BaseRepository;

class PosServiceRepository extends BaseRepository
{
    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return PartnerPosService::find($id);
    }

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

    /**
     * @param PartnerPosService $service
     * @return bool|null
     * @throws Exception
     */
    public function delete(PartnerPosService $service)
    {
        return $service->delete();
    }
}