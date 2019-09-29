<?php namespace Sheba\Repositories\Business;


use App\Models\InspectionSchedule;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\InspectionScheduleRepositoryInterface;

class InspectionScheduleRepository extends BaseRepository implements InspectionScheduleRepositoryInterface
{
    public function __construct(InspectionSchedule $inspection_schedule)
    {
        parent::__construct();
        $this->setModel($inspection_schedule);
    }

}