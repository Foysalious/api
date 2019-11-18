<?php namespace Sheba\OrderRequest\Repositories\Interfaces;

use Sheba\Repositories\Interfaces\BaseRepositoryInterface;

interface OrderRequestRepositoryInterface extends BaseRepositoryInterface
{
    public function load();
}
