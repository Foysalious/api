<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Collection;
use Sheba\Reward\Event\CampaignRule;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\ExcludedStatus;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\Gmv;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\Portal;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\Target;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\ComplainRatio;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\FiveStarRating;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\Rating;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\RatingPointRatio;
use Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter\ServeRatioFromSpro;
use Sheba\Reward\Event\TargetProgress;

class Rule extends CampaignRule
{
    /** @var Portal */
    public $portal;
    /** @var ExcludedStatus */
    public $excludedStatus;
    /** @var FiveStarRating */
    public $fiveStarRating;
    /** @var Rating */
    public $rating;
    /** @var RatingPointRatio */
    public $ratingPointRatio;
    /** @var ServeRatioFromSpro */
    public $serveRatioFromSpro;
    /** @var ComplainRatio */
    public $complainRatio;
    /** @var Gmv */
    public $gmv;

    public function validate()
    {
        $this->excludedStatus->validate();
        $this->portal->validate();
    }

    public function makeParamClasses()
    {
        $this->excludedStatus = new ExcludedStatus();
        $this->portal = new Portal();
        $this->target = new Target();
        $this->fiveStarRating = new FiveStarRating();
        $this->ratingPointRatio = new RatingPointRatio();
        $this->serveRatioFromSpro = new ServeRatioFromSpro();
        $this->complainRatio = new ComplainRatio();
        $this->rating = new Rating();
        $this->gmv = new Gmv();
        $this->setValues();
    }

    public function setValues()
    {
        $this->excludedStatus->value = property_exists($this->rule, 'excluded_status') ? $this->rule->excluded_status : null;
        $this->portal->value = property_exists($this->rule, 'portals') ? $this->rule->portals : null;
        $this->fiveStarRating->value = property_exists($this->rule, 'five_star_rating') ? $this->rule->five_star_rating : null;
        $this->rating->value = property_exists($this->rule, 'rating') ? $this->rule->rating : null;
        $this->ratingPointRatio->value = property_exists($this->rule, 'rating_point_ratio') ? (double)$this->rule->rating_point_ratio : null;
        $this->serveRatioFromSpro->value = property_exists($this->rule, 'serve_ratio_from_spro') ? (double)$this->rule->serve_ratio_from_spro : null;
        $this->complainRatio->value = property_exists($this->rule, 'complain_ratio') ? (double)$this->rule->complain_ratio : null;
        $this->gmv->value = property_exists($this->rule, 'gmv') ? (double)$this->rule->gmv : null;
    }

    public function check(Builder $query)
    {
        $this->excludedStatus->check($query);
        $this->portal->check($query);
        $this->fiveStarRating->check($query);
        $this->rating->check($query);
        $this->ratingPointRatio->check($query);
        $this->serveRatioFromSpro->check($query);
        $this->complainRatio->check($query);
    }


    public function getResourceIdsAfterCheckPercentageParams(Collection $jobs)
    {
        $rewardable_resources = [];
        foreach ($jobs->groupBy('resource_id') as $resource_id => $jobs) {
            if (count($jobs) < $this->target->value) continue;
            if (!$this->complainRatio->isCompleted($jobs, $this->target->value)) continue;
            if (!$this->serveRatioFromSpro->isCompleted($jobs, $this->target->value)) continue;
            if (!$this->ratingPointRatio->isCompleted($jobs, $this->target->value)) continue;
            if (!$this->gmv->isCompleted($jobs)) continue;
            array_push($rewardable_resources, $resource_id);
        }
        return $rewardable_resources;
    }

    public function getAchievedValue(Collection $jobs)
    {
        $total_job_count = $jobs->count();
        while ($total_job_count > 0) {
            if ($this->ratingPointRatio->isAchieved($jobs, $total_job_count) && $this->complainRatio->isAchieved($jobs, $total_job_count)
                && $this->serveRatioFromSpro->isAchieved($jobs, $total_job_count) && $this->gmv->isAchieved($jobs)) {
                break;
            }
            $total_job_count--;
        }
        return $total_job_count > $this->target->value ? $this->target->value : $total_job_count;
    }

    public function isTargetAchieved($achieved_value)
    {
        return $achieved_value >= $this->target->value;
    }

    public function getProgress(Builder $query): TargetProgress
    {
        $this->excludedStatus->check($query);
        $this->portal->check($query);
        $this->target->calculateProgress($query);

        return (new TargetProgress($this->target));
    }
}