<?php namespace Sheba\Repositories\Business;


use App\Models\ProcurementItem;
use Sheba\Repositories\Interfaces\ProcurementItemRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class ProcurementItemRepository extends BaseRepository implements ProcurementItemRepositoryInterface
{
    public function __construct(ProcurementItem $procurement_item)
    {
        parent::__construct();
        $this->setModel($procurement_item);
    }
}