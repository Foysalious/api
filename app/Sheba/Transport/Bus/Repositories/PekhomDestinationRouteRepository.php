<?php namespace Sheba\Transport\Bus\Repositories;

use App\Models\Transport\PekhomDestinationRoute;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Repositories\BaseRepository;

class PekhomDestinationRouteRepository extends BaseRepository
{
    /**
     * @return PekhomDestinationRoute[]|Collection
     */
    public function get()
    {
        return PekhomDestinationRoute::all();
    }

    /**
     * @param array $data
     * @return PekhomDestinationRoute
     */
    public function save(array $data)
    {
        return PekhomDestinationRoute::create($this->withCreateModificationField($data));
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        return PekhomDestinationRoute::insert($data);
    }

    /**
     * @param $id
     * @return PekhomDestinationRoute
     */
    public function findById($id)
    {
        return PekhomDestinationRoute::find($id);
    }

    /**
     * @param string $column_name
     * @param array $ids
     * @return Collection
     */
    public function findIdsByColumnName($column_name, $ids)
    {
        return PekhomDestinationRoute::whereIn($column_name,$ids)->get();
    }
}