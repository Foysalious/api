<?php namespace Sheba\Repositories\Business;

use App\Models\ProcurementItemField;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\ProcurementItemFieldRepositoryInterface;

class ProcurementItemFieldRepository extends BaseRepository implements ProcurementItemFieldRepositoryInterface
{
    public function __construct(ProcurementItemField $procurement_item_field)
    {
        parent::__construct();
        $this->setModel($procurement_item_field);
    }
}