<?php namespace Sheba\Repositories\Business;

use App\Models\HiredVehicle;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\HiredVehicleRepositoryInterface;

class HiredVehicleRepository extends BaseRepository implements HiredVehicleRepositoryInterface
{
    public function __construct(HiredVehicle $hired_vehicle)
    {
        parent::__construct();
        $this->setModel($hired_vehicle);
    }
}