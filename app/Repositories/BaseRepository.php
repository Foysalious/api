<?php namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

abstract class BaseRepository implements BaseRepositoryInterface
{
    use FileManager, CdnFileManager, ModificationFields;

    /** @var RequestIdentification $requestIdentification */
    protected $requestIdentification;

    /** @var Model $model */
    protected $model;

    /** @var string $modelClass */
    protected $modelClass;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->requestIdentification = app(RequestIdentification::class);
        $this->modelClass = get_class($model);
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes)
    {
        $model = $this->model->create($this->withCreateModificationField($attributes));
        return $model::find($model->id);
    }

    /**
     * @param $id
     * @return Model
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * @return Collection
     */
    public function getAll()
    {
        return $this->model->get();
    }


    /**
     * @param Model $model
     * @param array $data
     * @return Model
     * @throws ModelMismatch
     */
    public function update(Model $model, array $data)
    {
        $model->update($this->withUpdateModificationField($data));
        return $model;
    }

    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    protected function withRequestIdentificationData($data)
    {
        return $this->requestIdentification->set($data);
    }

}
