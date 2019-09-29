<?php namespace Sheba\Repositories\Business;

use App\Models\InspectionItem;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;
use DB;
use Sheba\Repositories\Interfaces\InspectionItemStatusLogRepositoryInterface;

class InspectionItemRepository extends BaseRepository implements InspectionItemRepositoryInterface
{
    private $inspectionItemStatusLogRepository;

    public function __construct(InspectionItem $inspection_item, InspectionItemStatusLogRepositoryInterface $inspection_item_status_log_repository)
    {
        parent::__construct();
        $this->setModel($inspection_item);
        $this->inspectionItemStatusLogRepository = $inspection_item_status_log_repository;
    }


    /**
     * @param $business_id
     * @return InspectionItemRepositoryInterface
     */
    public function getAllByBusiness($business_id)
    {
        return $this->model->whereHas('inspection', function ($q) use ($business_id) {
            return $q->where('business_id', $business_id);
        });
    }
}