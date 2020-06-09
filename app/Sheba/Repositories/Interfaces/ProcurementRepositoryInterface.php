<?php namespace Sheba\Repositories\Interfaces;

use Sheba\Business\Procurement\ProcurementFilterRequest;

interface ProcurementRepositoryInterface extends BaseRepositoryInterface
{
    public function ofBusiness($business_id);

    public function getProcurementFilterByLastDateOfSubmission();

    public function builder();

    public function filterWithTag($tag_id);

    public function filterWithCategory($category_ids);

    public function filterWithSharedTo($shared_to);

    public function filterWithEndDate($start_date, $end_date);

    public function filterWithEstimatedPrice($min_price, $max_price);

    public function getProcurementFilterBy(ProcurementFilterRequest $procurement_filter_request);
}
