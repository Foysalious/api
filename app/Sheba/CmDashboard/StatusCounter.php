<?php namespace Sheba\CmDashboard;

use Sheba\Dal\Category\Category;
use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Sheba\Helpers\TimeFrame;
use Sheba\Jobs\JobStatuses;

class StatusCounter
{
    private $user;
    private $category;
    private $counter;
    private $closedStatuses;
    /** @var TimeFrame */
    private $timeFrame;
    private $channel;

    public function __construct()
    {
        $this->counter = [];
        foreach (JobStatuses::get() as $status) {
            $this->counter[$status] = 0;
        }
        $this->counter += [
            'Total' => 0,
            'Open'  => 0,
        ];
        $this->closedStatuses = JobStatuses::getClosed();
    }

    public function forUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function forCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    public function timeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function channel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * COUNT JOB STATUS, IF USER IS_CM THEN CM JOB COUNT OR ALL SHEBA JOB COUNT.
     *
     * @return array
     */
    public function count()
    {
        $query = $this->filters($this->countQuery());
        return $this->get($query);
    }

    /**
     * COUNT JOB STATUS, IF USER IS_CM THEN CM JOB COUNT OR ALL SHEBA JOB COUNT.
     *
     * @return array
     */
    public function countOpen()
    {
        $query = $this->filters($this->openCountQuery());
        $result = $this->get($query);
        foreach ($this->closedStatuses as $closed_status) {
            unset($result[$closed_status]);
        }
        return $result;
    }

    /**
     * COUNT JOB STATUS, IF USER IS_CM THEN CM JOB COUNT OR ALL SHEBA JOB COUNT.
     *
     * @return array
     */
    public function countClosed()
    {
        $query = $this->filters($this->closedCountQuery());
        $result = $this->get($query);
        return array_only($result, array_merge($this->closedStatuses, ['Total']));
    }

    private function countQuery()
    {
        return Job::select('jobs.status', DB::raw('count(*) as count'))
            ->groupBy('jobs.status');
    }

    private function openCountQuery()
    {
        return $this->countQuery()->whereNotIn('jobs.status', $this->closedStatuses);
    }

    private function closedCountQuery()
    {
        return $this->countQuery()->whereIn('jobs.status', $this->closedStatuses);
    }

    private function filters($query)
    {
        if($this->user) $query = $query->forCM($this->user->id);
        if($this->category) $query = $query->forCategory($this->category->id);
        if($this->timeFrame) $query = $query->whereBetween('jobs.created_at', $this->timeFrame->getArray());
        if($this->channel) {
            $query = $query->join('partner_orders', 'partner_orders.id', '=', 'jobs.partner_order_id')
                ->join('orders', 'partner_orders.order_id', '=', 'orders.id')
                ->where('orders.sales_channel', $this->channel);
        }
        return $query;
    }

    private function get($query)
    {
        $counts = $query->get()->pluck('count', 'status')->toArray();
        $result = $counts + $this->counter;
        $result[JobStatuses::SERVED_AND_DUE] = $this->getServedAndDueJobCount();
        foreach ($result as $key => $value) {
            $result['Total'] += $value;
        }
        $result['Open'] = $result['Total'];
        foreach ($this->closedStatuses as $status) {
            $result['Open'] -= $result[$status];
        }
        return $result;
    }

    private function getServedAndDueJobCount()
    {
        $query = PartnerOrder::whereNotNull('closed_at')->whereNull('closed_and_paid_at');
        if($this->user || $this->category || $this->timeFrame) {
            $query = $query->join('jobs', 'partner_orders.id', '=', 'jobs.partner_order_id');
        }
        if($this->channel) {
            $query = $query->join('orders', 'partner_orders.order_id', '=', 'orders.id');
        }
        if($this->user) $query = $query->where('jobs.crm_id', $this->user->id);
        if($this->category) $query = $query->where('jobs.category_id', $this->category->id);
        if($this->timeFrame) $query = $query->whereBetween('jobs.created_at', $this->timeFrame->getArray());
        if($this->channel) $query = $query->where('orders.sales_channel', $this->channel);
        return $query->count();
    }
}