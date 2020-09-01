<?php namespace Sheba\Repositories\Interfaces\Business;

use Sheba\Repositories\Interfaces\BaseRepositoryInterface;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByNameOrAbbreviation($identity);
}