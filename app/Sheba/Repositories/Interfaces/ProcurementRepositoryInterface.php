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

    public function filterWithStatus($status);

    public function filterWithCreatedAt($start_date, $end_date);

    public function sortById($sort_by, $business_id);

    public function sortByTitle($sort_by, $business_id);

    public function sortByCreatedAt($sort_by, $business_id);
}
