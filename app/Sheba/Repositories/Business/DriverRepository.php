<?php namespace Sheba\Repositories\Business;

use App\Models\Driver;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\DriverRepositoryInterface;

class DriverRepository extends BaseRepository implements DriverRepositoryInterface
{
    public function __construct(Driver $driver)
    {
        parent::__construct();
        $this->setModel($driver);
    }
}