<?php namespace Sheba\Resource\InfoCalls;

use App\Models\Resource;
use Carbon\Carbon;

class InfoCallList
{
    /** @var Resource */
    private $resource;
    /** @var int $limit */
    private $limit;
    /** @var int $offset */
    private $offset;
    /** @var int $year */
    private $year;
    /** @var int $month */
    private $month;
    private $query;

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param $year
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @param $month
     * @return $this
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    public function getFilteredInfoCalls($query)
    {
        $query = $query->where('created_at', '>=', Carbon::now()->subMonth(12));
        if ($this->month) $query = $query
            ->whereYear('created_at', '=', $this->year)
            ->whereMonth('created_at', '=', $this->month);
        return $query;
    }

}