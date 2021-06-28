<?php namespace App\Sheba\Pos\Repositories\Interfaces;


use Sheba\Repositories\Interfaces\BaseRepositoryInterface;

interface PosServiceBatchRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllBatchesOfService($partner_id);
}