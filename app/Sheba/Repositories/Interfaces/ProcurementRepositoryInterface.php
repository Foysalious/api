<?php namespace Sheba\Repositories\Interfaces;

interface ProcurementRepositoryInterface extends BaseRepositoryInterface
{
    public function ofBusiness($business_id);
    public function builder();
}