<?php namespace Sheba\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes);

    public function createMany(array $attributes);

    /**
     * @param $id
     * @return mixed
     */
    public function find($id);


    /**
     * @param $column_name
     * @param $value
     * @return $this
     */
    public function where($column_name, $value);

    /**
     * @param array $column_name
     * @return  $this
     */
    public function select(array $column_name);

    /**
     * @return Collection
     */
    public function get();

    /**
     * @return Collection
     */
    public function getAll();

    /**
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $limit
     * @return mixed
     */
    public function paginate($limit);

    public function getByFieldOn($field, $data);
}
