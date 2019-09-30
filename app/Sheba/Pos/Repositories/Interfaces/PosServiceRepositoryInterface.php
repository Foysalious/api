<?php namespace Sheba\Pos\Repositories\Interfaces;

use Sheba\Repositories\Interfaces\BaseRepositoryInterface;

interface PosServiceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param $id
     * @return mixed
     */
    public function findWithTrashed($id);
}