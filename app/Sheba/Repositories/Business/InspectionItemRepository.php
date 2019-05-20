<?php namespace Sheba\Repositories\Business;

use App\Models\InspectionItem;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\InspectionRepositoryInterface;

class InspectionItemRepository extends BaseRepository implements InspectionRepositoryInterface
{
    public function __construct(InspectionItem $inspection_item)
    {
        parent::__construct();
        $this->setModel($inspection_item);
    }

}