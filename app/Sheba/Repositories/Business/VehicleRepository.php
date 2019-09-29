<?php namespace Sheba\Repositories\Business;

use App\Models\Vehicle;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\VehicleRepositoryInterface;

class VehicleRepository extends BaseRepository implements VehicleRepositoryInterface
{
    public function __construct(Vehicle $vehicle)
    {
        parent::__construct();
        $this->setModel($vehicle);
    }
}