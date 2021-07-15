<?php namespace App\Sheba\InventoryService\Services\SyncService;


class Service
{
    protected $client;
    protected $model;
    protected $modelName;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->setModelName(get_class($this->model));
        return $this;
    }

    /**
     * @param mixed $modelName
     */
    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
    }
}