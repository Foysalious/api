<?php namespace Sheba\Repositories\Business;


use App\Models\Procurement;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;
use Sheba\Repositories\BaseRepository;

class ProcurementRepository extends BaseRepository implements ProcurementRepositoryInterface
{
    public function __construct(Procurement $procurement)
    {
        parent::__construct();
        $this->setModel($procurement);
    }
}