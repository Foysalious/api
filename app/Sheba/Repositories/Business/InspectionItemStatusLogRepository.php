<?php namespace Sheba\Repositories\Business;


use App\Models\InspectionItemStatusLog;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\InspectionItemStatusLogRepositoryInterface;

class InspectionItemStatusLogRepository extends BaseRepository implements InspectionItemStatusLogRepositoryInterface
{

    public function __construct(InspectionItemStatusLog $inspection_item_status_log)
    {
        parent::__construct();
        $this->setModel($inspection_item_status_log);
    }
}