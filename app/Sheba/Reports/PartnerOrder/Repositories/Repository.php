<?php namespace Sheba\Reports\PartnerOrder\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Sheba\Helpers\TimeFrame;
use Sheba\Reports\LifetimeQueryHandler;

abstract class Repository
{
    use LifetimeQueryHandler;

    protected $timeline;
    protected $partner;
    /** @var TimeFrame */
    protected $cancelledDateRange;
    /** @var TimeFrame */
    protected $closedDateRange;

    /** @var array */
    protected $ids = [];
    protected $limit;
    protected $offset;

    /**
     * @param mixed $timeline
     * @return Repository
     */
    public function setTimeline($timeline)
    {
        $this->timeline = $timeline;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return Repository
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param TimeFrame $cancelled_date_range
     * @return Repository
     */
    public function setCancelledDateRange(TimeFrame $cancelled_date_range)
    {
        $this->cancelledDateRange = $cancelled_date_range;
        return $this;
    }

    /**
     * @param TimeFrame $closed_date_range
     * @return Repository
     */
    public function setClosedDateRange(TimeFrame $closed_date_range)
    {
        $this->closedDateRange = $closed_date_range;
        return $this;
    }

    public function setIds(array $ids)
    {
        $this->ids = $ids;
        return $this;
    }

    public function setLimitOffset($limit, $offset)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return Collection
     */
    public function get()
    {
        $query = $this->getQuery();
        $query = $this->filterTimeLine($query);
        $query = $this->filterPartner($query);
        $query = $this->filterClosedDate($query);
        $query = $this->filterCancelledDate($query);
        $query = $this->filterIds($query);
        $query = $this->paginate($query);
        return $query->get();
    }

    /**
     * @return Builder
     */
    abstract protected function getQuery();

    /**
     * @return string
     */
    abstract protected function getPartnerIdField();

    /**
     * @return string
     */
    abstract protected function getCancelledDateField();

    /**
     * @return string
     */
    abstract protected function getClosedDateField();

    protected function filterTimeLine(Builder $query)
    {
        if (!$this->timeline) return $query;
        return $this->notLifetimeQuery($query, $this->timeline['timeline'], $this->timeline['field']);
    }

    protected function filterPartner(Builder $query)
    {
        if (!$this->partner) return $query;
        return $query->where($this->getPartnerIdField(), $this->partner);
    }

    protected function filterCancelledDate(Builder $query)
    {
        if (!$this->cancelledDateRange) return $query;
        return $this->filterDate($query, $this->closedDateRange, $this->getCancelledDateField());
    }

    protected function filterClosedDate(Builder $query)
    {
        if (!$this->closedDateRange) return $query;
        return $this->filterDate($query, $this->cancelledDateRange, $this->getClosedDateField());
    }

    protected function filterDate(Builder $query, TimeFrame $range, $field)
    {
        return !$range->hasDates() ? $query : $query->whereBetween($field, $range->getArray());
    }

    protected function filterIds(Builder $query)
    {
        if (empty($this->ids)) return $query;
        return $query->whereIn('id', $this->ids);
    }

    protected function paginate(Builder $query)
    {
        if (!$this->limit) return $query;
        return $query->skip($this->offset)->take($this->limit);
    }
}