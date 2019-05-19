<?php


namespace App\Repositories\Business;


use App\Models\FormTemplateItem;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\FormTemplateItemRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FormTemplateItemRepository extends BaseRepository implements FormTemplateItemRepositoryInterface
{

    public function __construct(FormTemplateItem $formTemplateItem)
    {
        parent::__construct($formTemplateItem);
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes)
    {
        // TODO: Implement create() method.
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function paginate($limit)
    {
        // TODO: Implement paginate() method.
    }

    public function getByFieldOn($field, $data)
    {
        // TODO: Implement getByFieldOn() method.
    }
}