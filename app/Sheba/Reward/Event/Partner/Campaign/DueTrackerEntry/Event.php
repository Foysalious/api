<?php

namespace Sheba\Reward\Event\Partner\Campaign\DueTrackerEntry;

use App\Models\Job;
use App\Models\Partner;
use App\Models\PosOrder;
use Illuminate\Support\Collection;
use Sheba\Dal\Expense\Expense;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Event\Partner\Campaign\DueTrackerEntry\Rule;
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
            throw new RulesTypeMismatchException("Pos entry event must have an event rule");

        return parent::setRule($rule);
    }

    public function findRewardableUsers(Collection $users = null)
    {
        $this->initiateQuery();

        if (!empty($users)) {
            $this->filterPartner($users->pluck('id')->toArray());
        }

        $this->filterConstraints();

        $this->rule->check($this->query);

        return Partner::whereIn('id', $this->query->pluck('partner_orders.partner_id')->toArray())->get();
    }

    /**
     * Return count or percentage.
     * @param Rewardable $rewardable
     * @return TargetProgress
     */
    public function checkProgress(Rewardable $rewardable)
    {
        $this->initiateQuery();
        $this->filterPartner([$rewardable->id]);
        $this->filterConstraints();

        return $this->rule->getProgress($this->query);
    }

    private function initiateQuery()
    {
        $target = $this->rule->target->getTarget();
        $table = config('database.connections.mysql.database');
        $timeFrame = $this->timeFrame->getDateStringArray();
        $query1 = "SELECT $table.partners.id as partner_id, expense.entries.account_id as expense_account_id, count(expense.entries.id) as target";
        $query2 = "FROM expense.entries";
        $query3 = "join $table.partners on sheba_fresh.partners.expense_account_id = expense.entries.account_id";
        $query4 = "where expense.entries.created_at between '$timeFrame[0]' and '$timeFrame[1]'";
        $query5 = "group by expense.entries.account_id";
        $query6 = "having target > $target";
        $this->query = "$query1 $query2 $query3 $query4 $query5 $query6";
    }

    private function filterPartner(array $partners)
    {
        $this->query->whereIn('partner_orders.partner_id', $partners);
    }

    private function filterConstraints()
    {
        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'Sheba\Dal\Category\Category') {
                $this->query->whereIn('category_id', $ids);
            } elseif ($key == 'App\Models\PartnerSubscriptionPackage') {
                $this->query->join('partners', 'partners.id', '=', 'partner_orders.partner_id')
                    ->whereIn('partners.package_id', $ids);
            }
        }
    }

    function getParticipatedUsers()
    {
        $this->initiateQuery();

        $dueEntries = \DB::select($this->query);


        if (count($dueEntries) == 0) return [];

        $participated_users = [];

        foreach ($dueEntries as $dueEntry) {
            $partner = Partner::where('id', $dueEntry->partner_id)->first();

            $participated_user = new ParticipatedCampaignUser();
            $participated_user->setAchievedValue($dueEntry->target)->setUser($partner)->setIsTargetAchieved($this->rule->isTargetAchieved($dueEntry->target));
            array_push($participated_users, $participated_user);
        }

        return $participated_users;
    }
}