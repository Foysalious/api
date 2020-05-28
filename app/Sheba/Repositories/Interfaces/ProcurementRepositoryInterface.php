<?php namespace Sheba\Repositories\Interfaces;

interface ProcurementRepositoryInterface extends BaseRepositoryInterface
{
    public function ofBusiness($business_id);
    public function getProcurementFilterByLastDateOfSubmission();
    public function builder();
    public function filterWithTag($tag_id);
    public function filterWithCategory($category_id);
    public function filterWithSharedTo($shared_to);
    public function filterWithEndDate($start_date, $end_date);
    public function filterWithEstimatedPrice($min_price, $max_price);
}