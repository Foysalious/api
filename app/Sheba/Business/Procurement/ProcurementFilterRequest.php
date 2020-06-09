<?php namespace Sheba\Business\Procurement;

use Carbon\Carbon;
use Sheba\Helpers\TimeFrame;

class ProcurementFilterRequest
{
    private $categoriesId;
    private $tagsId;
    private $startDate;
    private $endDate;
    private $minPrice;
    private $maxPrice;
    private $sharedTo;
    private $searchQuery;

    /**
     * @return mixed
     */
    public function getCategoriesId()
    {
        return $this->categoriesId;
    }

    /**
     * @param $categories_id
     * @return ProcurementFilterRequest
     */
    public function setCategoriesId(array $categories_id)
    {
        $this->categoriesId = $categories_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTagsId()
    {
        return $this->tagsId;
    }

    /**
     * @param array $tags_id
     * @return ProcurementFilterRequest
     */
    public function setTagsId(array $tags_id)
    {
        $this->tagsId = $tags_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param Carbon $start_date
     * @return ProcurementFilterRequest
     */
    public function setStartDate(Carbon $start_date)
    {
        $this->startDate = $start_date->startOfDay();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @param $min_price
     * @return ProcurementFilterRequest
     */
    public function setMinPrice($min_price)
    {
        $this->minPrice = $min_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSharedTo()
    {
        return $this->sharedTo;
    }

    /**
     * @param string $shared_to
     * @return ProcurementFilterRequest
     */
    public function setSharedTo($shared_to)
    {
        $this->sharedTo = $shared_to;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param Carbon $end_date
     * @return ProcurementFilterRequest
     */
    public function setEndDate(Carbon $end_date)
    {
        $this->endDate = $end_date->endOfDay();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * @param mixed $maxPrice
     * @return ProcurementFilterRequest
     */
    public function setMaxPrice($maxPrice)
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * @param $search_query
     * @return ProcurementFilterRequest
     */
    public function setSearchQuery($search_query)
    {
        $this->searchQuery = $search_query;
        return $this;
    }
}
