<?php namespace Sheba\Repositories\Business;

use App\Models\FuelLog;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\FuelLogRepositoryInterface;

class FuelLogRepository extends BaseRepository implements FuelLogRepositoryInterface
{

    public function __construct(FuelLog $fuel_log)
    {
        parent::__construct();
        $this->setModel($fuel_log);
    }
}