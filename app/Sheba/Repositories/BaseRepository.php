<?php namespace Sheba\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Sheba\FileManagers\FileManager;
use Sheba\FileManagers\CdnFileManager;
use Sheba\ModificationFields;
use Sheba\Pos\Payment\Creator as PaymentCreator;
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
    /**
     * @var PaymentCreator
     */
    protected $paymentCreator;

    public function __construct(PaymentCreator $paymentCreator)
    {
        $this->requestIdentification = new RequestIdentification();
        $this->paymentCreator = $paymentCreator;
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
        return ($this->model)::insert($data);
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
     * @param $column_name
     * @param $value
     * @return $this
     */
    public function where($column_name, $value)
    {
        return $this->model->where($column_name, $value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function whereRaw($value)
    {
        $this->model->whereRaw($value);
    }

    /**
     * @param $column_name
     * @param array $value
     * @return $this
     */
    public function whereIn($column_name, array $value)
    {
        return $this->model->whereIn($column_name, $value);
    }

    /**
     * @param $column_name
     * @param array $value
     * @return $this
     */
    public function whereBetween($column_name, array $value)
    {
        return $this->model->whereBetween($column_name, $value);
    }

    /**
     * @param $column_name
     * @param $value
     * @return BaseRepositoryInterface
     */
    public function whereLike($column_name, $value)
    {
        return $this->model->where($column_name, 'like', '%' . $value . '%');
    }

    /**
     * @param array $column_name
     * @return $this
     */
    public function select(array $column_name)
    {
        $select = $column_name[0];
        foreach (array_slice($column_name, 1) as $column) {
            $select .= ',' . $column;
        }
        return $this->model->select($select);
    }

    /**
     * @return Collection
     */
    public function get()
    {
        return $this->model->get();
    }

    public function first()
    {
        return $this->model->first();
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

    /**
     * @param $column_name
     * @param $value
     * @return $this
     */
    public function orWhere($column_name, $value)
    {
        return $this->model->orWhere($column_name, $value);
    }

    public function builder()
    {
        return $this->model->newQuery();
    }

}
