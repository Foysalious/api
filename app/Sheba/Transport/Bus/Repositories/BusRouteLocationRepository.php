<?php namespace Sheba\Transport\Bus\Repositories;

use App\Models\BusRouteLocation;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Repositories\BaseRepository;

class BusRouteLocationRepository extends BaseRepository
{
    /**
     * @return BusRouteLocation[]|Collection
     */
    public function get()
    {
        return BusRouteLocation::all();
    }

    /**
     * @param array $data
     * @return BusRouteLocation
     */
    public function save(array $data)
    {
        return BusRouteLocation::create($this->withCreateModificationField($data));
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return BusRouteLocation::insert($data);
    }
}