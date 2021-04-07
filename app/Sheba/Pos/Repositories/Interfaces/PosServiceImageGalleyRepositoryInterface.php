<?php namespace Sheba\Pos\Repositories\Interfaces;


use Sheba\Repositories\Interfaces\BaseRepositoryInterface;

interface PosServiceImageGalleyRepositoryInterface extends BaseRepositoryInterface
{
    public function findWithTrashed($id);
}