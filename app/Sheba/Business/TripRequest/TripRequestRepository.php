<?php namespace Sheba\Business\TripRequest;


use App\Models\BusinessTripRequest;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\Business\TripRequestRepositoryInterface;

class TripRequestRepository extends BaseRepository implements TripRequestRepositoryInterface
{

    public function __construct(BusinessTripRequest $trip_request)
    {
        parent::__construct();
        $this->setModel($trip_request);
    }
}