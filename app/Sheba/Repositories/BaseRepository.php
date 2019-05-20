<?php namespace Sheba\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sheba\FileManagers\FileManager;
use Sheba\FileManagers\CdnFileManager;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BaseRepositoryInterface;
use Sheba\RequestIdentification;

class BaseRepository implements BaseRepositoryInterface
{
    use FileManager, CdnFileManager, ModificationFields;

    protected $partnerLoggedIn = true;

    /** @var RequestIdentification $requestIdentification */
    protected $requestIdentification;

    /** @var Model $model */
    protected $model;

    /** @var string $modelClass */
    protected $modelClass;

    public function __construct()
    {
        $this->requestIdentification = new RequestIdentification();
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
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

    public function createMany(array $attributes)
    {
        $data = [];
        foreach ($attributes as $attribute) {
            array_push($data, $this->withCreateModificationField($attribute));
        }
        ($this->model)::insert($data);
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
       return ($this->model)::find($id)->delete();
    }

    protected function withRequestIdentificationData($data)
    {
        return $this->requestIdentification->set($data);
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