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

    public function delete($service)
    {
        return $service->delete();
    }
}