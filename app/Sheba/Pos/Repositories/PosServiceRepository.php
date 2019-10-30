<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosService;
use Exception;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class PosServiceRepository extends BaseRepository implements PosServiceRepositoryInterface
{
    public function __construct(PartnerPosService $partnerPosService)
    {
        parent::__construct();
        $this->setModel($partnerPosService);
    }

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

    /**
     * @param $id
     * @return mixed
     */
    public function findWithTrashed($id)
    {
        return PartnerPosService::withTrashed()->find($id);
    }
}