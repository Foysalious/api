<?php namespace Sheba\Cache\Schema;

use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Cache\Schema\DataBuilder\CategorySchema;
use Sheba\Cache\Schema\DataBuilder\ServiceSchema;


class SchemaDataStore implements DataStoreObject
{
    /** @var SchemaCacheRequest */
    private $schemaCacheRequest;
    private $serviceSchema;
    private $categorySchema;

    public function __construct(ServiceSchema $service_schema, CategorySchema $category_schema)
    {
        $this->serviceSchema = $service_schema;
        $this->categorySchema = $category_schema;
    }


    public function setCacheRequest(CacheRequest $request)
    {
        $this->schemaCacheRequest = $request;
        return $this;
    }

    public function generate()
    {
        $data = null;
        $model = ("App\\Models\\" . ucfirst($this->schemaCacheRequest->getType()))::find($this->schemaCacheRequest->getTypeId());
        if (!$model) return $data;
        elseif ($model instanceof Category) $data = $this->categorySchema->setCategory($model)->get();
        elseif ($model instanceof Service) $data = $this->serviceSchema->setService($model)->get();
        return $data;
    }
}