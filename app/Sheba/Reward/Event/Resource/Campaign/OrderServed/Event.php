<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed;

use App\Models\Job;

use App\Models\Resource;

use Illuminate\Support\Collection;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Event\TargetProgress;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Campaign
{
    private $query;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Order served event must have a order serve event rule");

        return parent::setRule($rule);
    }

    public function findRewardableUsers(Collection $users = null)
    {
        $this->initiateQuery();
        $this->filterConstraints();
        $this->rule->check($this->query);
        $jobs = $this->query->get();
        $ids = $this->rule->getResourceIdsAftercheckPercentageParams($jobs);
        return Resource::whereIn('id', $ids)->get();
    }


    private function initiateQuery()
    {
        $this->query = Job::where('jobs.status', 'Served')
            ->join('partner_orders', 'partner_orders.id', '=', 'jobs.partner_order_id')
            ->whereBetween('delivered_date', $this->timeFrame->getArray());
    }

    private function filterResource(array $resources)
    {
        $this->query->whereIn('jobs.resource_id', $resources);
    }

    private function filterConstraints()
    {
        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'App\Models\Category') {
                $this->query->whereIn('category_id', $ids);
            } elseif ($key == 'App\Models\PartnerSubscriptionPackage') {
                $this->query->join('partners', 'partners.id', '=', 'partner_orders.partner_id')
                    ->whereIn('partners.package_id', $ids);
            }
        }
    }

    /**
     * Return count or percentage.
     * @param Rewardable $rewardable
     * @return TargetProgress
     */
    public function checkProgress(Rewardable $rewardable)
    {
        $jobs = $this->getJobs($rewardable);
        $achieved = $this->rule->getAchievedValue($jobs);
        $this->rule->target->setAchieved($achieved);
        return (new TargetProgress($this->rule->target));
    }

    /**
     * @return ParticipatedCampaignUser[]|[]
     */
    public function getParticipatedUsers()
    {
        $jobs = $this->getJobs();
        if ($jobs->count() == 0) return [];
        $group_by_resources = $jobs->groupBy('resource_id');
        $resources = Resource::whereIn('id', $group_by_resources->keys()->toArray())->get();
        $participated_users = [];
        foreach ($resources as $resource) {
            $participated_user = new ParticipatedCampaignUser();
            $achieved = $this->rule->getAchievedValue($group_by_resources->get($resource->id));
            $participated_user->setAchievedValue($achieved)->setUser($resource)->setIsTargetAchieved($this->rule->isTargetAchieved($achieved));
            array_push($participated_users, $participated_user);
        }
        return $participated_users;
    }

    private function getJobs(Rewardable $rewardable = null)
    {
        $this->initiateQuery();
        if ($rewardable) $this->filterResource([$rewardable->id]);
        $this->filterConstraints();
        $this->rule->check($this->query);
        return $this->query->get();
    }
}