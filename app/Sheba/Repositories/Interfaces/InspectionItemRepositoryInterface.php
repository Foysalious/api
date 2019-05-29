<?php namespace Sheba\Repositories\Interfaces;

interface InspectionItemRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param $business_id
     * @return $this
     */
    public function getAllByBusiness($business_id);
}