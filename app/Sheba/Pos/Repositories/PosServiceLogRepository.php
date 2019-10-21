<?php namespace Sheba\Pos\Repositories;

use App\Models\PartnerPosService;
use App\Models\PartnerPosServiceLog;
use Sheba\Pos\Repositories\Interfaces\PosServiceLogRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class PosServiceLogRepository extends BaseRepository implements PosServiceLogRepositoryInterface
{
    /**
     * PosServiceLogRepository constructor.
     * @param PartnerPosServiceLog $partner_pos_service_log
     */
    public function __construct(PartnerPosServiceLog $partner_pos_service_log)
    {
        parent::__construct();
        $this->setModel($partner_pos_service_log);
    }
}