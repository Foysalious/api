<?php namespace Sheba\Repositories\Business;


use App\Models\Inspection;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\InspectionItemRepositoryInterface;

class InspectionRepository extends BaseRepository implements InspectionItemRepositoryInterface
{

    public function __construct(Inspection $inspection)
    {
        parent::__construct();
        $this->setModel($inspection);
    }

}