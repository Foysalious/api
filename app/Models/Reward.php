<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Helpers\TimeFrame;
use Sheba\Reward\ActionEventInitiator;
use Sheba\Reward\CampaignEventInitiator;
use \Sheba\Dal\RewardTargets\Model as RewardTargets;

class Reward extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_time', 'end_time', 'valid_till_date'];
    protected $casts = ['amount' => 'double'];

    public function detail()
    {
        return $this->morphTo();
    }

    public function constraints()
    {
        return $this->hasMany(RewardConstraint::class);
    }

    public function rewardTargets()
    {
        return $this->hasMany(RewardTargets::class);
    }

    public function noConstraints()
    {
        return $this->hasMany(RewardNoConstraint::class);
    }

    public function isCampaign()
    {
        return $this->detail_type == 'App\Models\RewardCampaign';
    }

    public function isAction()
    {
        return $this->detail_type == 'App\Models\RewardAction';
    }

    public function scopeOngoing($query)
    {
        return $query->where([['start_time', '<=', Carbon::today()], ['end_time', '>=', Carbon::today()]]);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('end_time', '>=', Carbon::today());
    }

    public function scopeForPartner($query)
    {
        return $query->where('target_type', 'App\Models\Partner');
    }

    public function scopeForResource($query)
    {
        return $query->where('target_type', 'App\Models\Resource');
    }

    public function scopeTypeCampaign($query)
    {
        return $query->where('detail_type', 'App\Models\RewardCampaign');
    }

    public function scopeTypeAction($query)
    {
        return $query->where('detail_type', 'App\Models\RewardAction');
    }

    public function getAmount()
    {
        if ($this->isPercentageble()) {
            if (!$this->actionEvent)
                return throwException('Action Event Not Found');

            return $this->actionEvent->calculateAmount();
        }

        return $this->amount;
    }

    public function setActionEvent(array $params)
    {
        $this->actionEvent = app(ActionEventInitiator::class)
            ->setReward($this)
            ->setName($this->detail->event_name)
            ->setRule($this->detail->event_rules)
            ->setParams($params)
            ->initiate();

        return $this;
    }

    public function setCampaignEvents()
    {
        $this->campaignEvents = collect([]);
        foreach (json_decode($this->detail->events) as $event_name => $event_rule) {
            $event = app(CampaignEventInitiator::class)->setReward($this)->setName($event_name)->setRule($event_rule)->initiate();
            $this->campaignEvents->push($event);
        }

        return $this;
    }

    public function isCashType()
    {
        return $this->type == constants('REWARD_TYPE')['Cash'];
    }

    public function isPointType()
    {
        return $this->type == constants('REWARD_TYPE')['Point'];
    }

    public function isValidityApplicable()
    {
        return $this->valid_till_date || $this->valid_till_day;
    }

    private function isPercentageble()
    {
        return $this->is_amount_percentage && $this->detail_type == constants('REWARD_DETAIL_TYPE')['Action'];
    }

    public function isCustomer()
    {
        return $this->target_type == "App\\Models\\Customer";
    }

    public function getTerms()
    {
        return $this->terms && json_decode($this->terms) > 0 ? json_decode($this->terms) : [];
    }


    public function getUserFilters(): array
    {
        return json_decode($this->user_filters, 1);
    }

    public function getActiveStatusUserFilterTimeFrame()
    {
        $user_filters = $this->getUserFilters();
        if (!array_key_exists("active_status", $user_filters)) return null;

        if ($user_filters['active_status'] == 'last7') {
            return (new TimeFrame())->for7DaysBefore($this->created_at);
        } else if ($user_filters['active_status'] == 'last30') {
            return (new TimeFrame())->for30DaysBefore($this->created_at);
        }

        return null;
    }

    public function getRegistrationWithinUserFilterTimeFrame()
    {
        $user_filters = $this->getUserFilters();
        if (!array_key_exists("registration_within", $user_filters)) return null;

        $start = $user_filters["registration_within"]['start'] . " 00:00:00";
        $end = $user_filters["registration_within"]['end'] . " 23:59:59";
        return new TimeFrame($start, $end);
    }
}
