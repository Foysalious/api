<?php namespace Sheba\Cache\Schema;

use App\Models\Category;
use App\Models\Service;
use Sheba\Cache\DataStoreObject;
use Sheba\Schema\CategorySchema;
use Sheba\Schema\ServiceSchema;

class SchemaDataStoreObject extends DataStoreObject
{
    private $serviceSchema;
    private $categorySchema;
    /** @var Category|Service */
    private $model;

    public function __construct(ServiceSchema $service_schema, CategorySchema $category_schema)
    {
        $this->serviceSchema = $service_schema;
        $this->categorySchema = $category_schema;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function generateData()
    {
        if (!$this->model) $data = ['code' => 404, 'message' => 'Not found'];
        elseif ($this->model instanceof Category) $data = $this->categorySchema->setCategory($this->model)->get();
        else $data = $this->serviceSchema->setService($this->model)->get();
        $this->setData($data);
    }
}