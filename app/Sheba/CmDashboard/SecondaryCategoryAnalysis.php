<?php namespace Sheba\CmDashboard;

use Illuminate\Support\Collection;
use Sheba\Dal\Category\Category;
use App\Models\Location;
use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Helpers\TimeFrame;
use Sheba\CmDashboard\StatusCounter as JobStatusCounter;

class SecondaryCategoryAnalysis
{
    private $category;
    private $categoryId;
    private $month;
    private $year;
    /** @var TimeFrame */
    private $timeFrame;
    /** @var JobStatusCounter */
    private $statusCounter;

    private $jobStatuses;
    private $jobStatusCounts;
    private $partnerLocationsCount;
    private $commissionsCount;
    private $minPartnerLocations;
    private $maxPartnerLocations;
    private $minPartnerChanges;
    private $maxPartnerChanges;
    private $minPartnerChangeCount;
    private $maxPartnerChangeCount;
    private $partners;
    private $avgSpChanges;
    private $cancelReasons;

    public function __construct(JobStatusCounter $status_counter)
    {
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->month;
        $this->statusCounter = $status_counter;

        $this->jobStatuses = constants('JOB_STATUSES');
        $this->commissionsCount = collect([]);
        $this->cancelReasons = collect([]);
    }

    public function category($category_id)
    {
        $this->categoryId = $category_id;
        $this->category = Category::find($category_id);
        return $this;
    }

    public function month($month)
    {
        $this->month = $month;
        return $this;
    }

    public function year($year)
    {
        $this->year = $year;
        return $this;
    }

    public function get()
    {
        $this->timeFrame = (new TimeFrame())->forAMonth($this->month, $this->year);
        $this->getPartners();
        $this->calculateJobStatuses();
        $this->calculateLocations();
        $this->calculateCommissions();
        $this->calculateSpChanges();
        $this->calculateCancelReasons();
        return $this->formatData();
    }

    private function getPartners()
    {
        $this->partners = DB::table('category_partner')->select('partner_id')
            ->where('category_id', $this->categoryId)->pluck('partner_id')->all();
    }

    private function calculateJobStatuses()
    {
        if($this->category) $this->statusCounter->forCategory($this->category);
        $this->jobStatusCounts = $this->statusCounter->timeFrame($this->timeFrame)->count();
    }

    private function calculateLocations()
    {
        $data = DB::table('location_partner')->select(DB::raw('location_id, COUNT(partner_id) as partner_count'))
            ->whereIn('partner_id', $this->partners)->groupBy('location_id')->get();

        $this->partnerLocationsCount['min'] = $data->min('partner_count') ?: 0;
        $this->partnerLocationsCount['max'] = $data->max('partner_count') ?: 0;
        $this->partnerLocationsCount['avg'] = $data->avg('partner_count') ?: 0;
        $this->minPartnerLocations = $this->getPartnerLocationsOnCount($data, 'min');
        $this->maxPartnerLocations = $this->getPartnerLocationsOnCount($data, 'max');
    }

    private function getPartnerLocationsOnCount(Collection $data, $count_type)
    {
        return $data->where('partner_count', $this->partnerLocationsCount[$count_type])
            ->pluck('location_id')->map(function ($location_id) {
                return ['id' => $location_id, 'name' => Location::select('name')->find($location_id)->name];
            })->pluck('name', 'id');
    }

    private function calculateCommissions()
    {
        $this->commissionsCount = DB::table('category_partner')->select(DB::raw('commission, COUNT(partner_id) as partner_count'))
            ->where('category_id', $this->categoryId)->groupBy('commission')->get()->pluck('partner_count', 'commission');
    }

    private function calculateSpChanges()
    {
        $data = DB::table('job_partner_change_logs')->select(DB::raw('old_partner_id, COUNT(id) as count'))
            ->whereIn('job_id', function ($q) {
                return $q->select('id')->from('jobs')->where('category_id', $this->categoryId);
            })->groupBy('old_partner_id');
        if($this->timeFrame) $data = $data->whereBetween('created_at', $this->timeFrame->getArray());
        $data = collect($data->get());
        $total_changes = $data->sum('count');
        $total_partner = $data->count();
        $this->minPartnerChangeCount = $data->min('count');
        $this->maxPartnerChangeCount = $data->max('count');
        $this->minPartnerChanges = $this->getChangedPartnerOnCount($data, $this->minPartnerChangeCount);
        $this->maxPartnerChanges = $this->getChangedPartnerOnCount($data, $this->maxPartnerChangeCount);
        $this->avgSpChanges = $total_partner ? ($total_changes / $total_partner) : 0;
    }

    private function getChangedPartnerOnCount(Collection $data, $count)
    {
        return $data->where('count', $count)
            ->pluck('old_partner_id')->map(function ($location_id) {
                return ['id' => $location_id, 'name' => Partner::select('name')->find($location_id)->name];
            })->pluck('name', 'id');
    }

    private function calculateCancelReasons()
    {
        $data = DB::table('job_cancel_logs')->select(DB::raw('cancel_reason, COUNT(id) as count'))
            ->whereIn('job_id', function ($q) {
                return $q->select('id')->from('jobs')->where('category_id', $this->categoryId);
            })->groupBy('cancel_reason');
        if($this->timeFrame) $data = $data->whereBetween('created_at', $this->timeFrame->getArray());

        $this->cancelReasons = $data->get()->pluck('count', 'cancel_reason');
    }

    private function formatData()
    {
        $formatted_commission = $this->commissionsCount->map(function ($count, $commission) {
            return [$commission, $count];
        })->values();

        $formatted_cancel_reason = $this->cancelReasons->map(function ($count, $commission) {
            return [$commission, $count];
        })->values();

        return [
            'served_order'          => $this->jobStatusCounts[$this->jobStatuses['Served']],
            'created_order'         => $this->jobStatusCounts['Total'],
            'open_order'            => $this->jobStatusCounts['Open'],
            'avg_sp_change'         => number_format($this->avgSpChanges, 2),
            'max_sp_changes'        => $this->maxPartnerChangeCount ? $this->maxPartnerChangeCount . ' by ' . $this->maxPartnerChanges->implode(', ') : "N/A",
            'cancel_order'          => $this->jobStatusCounts[$this->jobStatuses['Cancelled']],
            'min_sp_per_location'   => $this->partnerLocationsCount['min'] . ' in ' . $this->minPartnerLocations->implode(', '),
            'avg_commission'        => number_format($this->commissionsCount->keys()->avg(), 2),
            'cancel_reasons'        => $formatted_cancel_reason,
            'commission'            => $formatted_commission
        ];
    }
}
