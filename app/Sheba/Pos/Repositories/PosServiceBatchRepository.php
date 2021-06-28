<?php namespace App\Sheba\Pos\Repositories;


use App\Repositories\BaseRepository;

class PosServiceBatchRepository
{
    protected $model;

    public function __construct(PosServiceBatchRepository $model)
    {
        $this->model = $model;
    }

    public function getAllBatchesOfService($service_id)
    {
        return $this->model->where('partner_pos_service_id', $service_id)->get();
    }
}