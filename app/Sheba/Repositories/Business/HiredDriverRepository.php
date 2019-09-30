<?php namespace Sheba\Repositories\Business;

use App\Models\HiredDriver;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\HiredDriverRepositoryInterface;

class HiredDriverRepository extends BaseRepository implements HiredDriverRepositoryInterface
{
    public function __construct(HiredDriver $hired_driver)
    {
        parent::__construct();
        $this->setModel($hired_driver);
    }
}