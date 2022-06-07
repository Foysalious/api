<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Helpers\TimeFrame;
use Sheba\Reward\ActionEventInitiator;
use Sheba\Reward\CampaignEventInitiator;
use Sheba\Dal\RewardTargets\Model as RewardTargets;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Campaign;

class Reward extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_time', 'end_time', 'valid_till_date'];
    protected $casts = ['amount' => 'double'];

    /** @var Action */
    public $actionEvent;
    /** @var Campaign */
    public $campaignEvents;

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

    public function categoryConstraints()
    {
        return $this->hasMany(RewardConstraint::class)->where('constraint_type', 'Sheba\\Dal\\Category\\Category');
    }

    public function categoryNoConstraints()
    {
        return $this->hasMany(RewardNoConstraint::class)->where('constraint_type', 'Sheba\\Dal\\Category\\Category');
    }

    public function isCampaign()
    {
        return $this->detail_type == RewardCampaign::class;
    }

    public function isAction()
    {
        return $this->detail_type == RewardAction::class;
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
        return $query->where('target_type', Partner::class);
    }

    public function scopeForResource($query)
    {
        return $query->where('target_type', Resource::class);
    }

    public function scopeTypeCampaign($query)
    {
        return $query->where('detail_type', RewardCampaign::class);
    }

    public function scopeTypeAction($query)
    {
        return $query->where('detail_type', RewardAction::class);
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

    public function calculateAmountWRTActionValue($value)
    {
        if (!$this->is_amount_percentage) return $this->amount;

        $amount = ($this->amount * $value) / 100;
        return $this->cap ? min($amount, $this->cap) : $amount;
    }

    public function setActionEvent(array $params)
    {
        /** @var ActionEventInitiator $initiator */
        $initiator = app(ActionEventInitiator::class);
        $this->actionEvent = $initiator
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

    public function offer()
    {
        return $this->hasMany(OfferShowcase::class, 'target_id')->where('target_type', 'App\\Models\\Reward');
    }

    /**
     * @param $query
     * @param  Carbon  $data_time
     * @return mixed
     */
    public function scopeOngoingBySpecificDateAndTime($query, Carbon $data_time)
    {
        return $query->where([['start_time', '<=', $data_time], ['end_time', '>=', $data_time]]);
    }

    /**
     * @param $query
     * @param  Carbon  $data_time
     * @return mixed
     */
    public function scopeInvalidBySpecificDateAndTime($query, Carbon $data_time)
    {
        return $query->where('start_time', '>', $data_time)->orWhere('end_time', '<', $data_time);
    }

    public function isCustomer()
    {
        return $this->target_type == Customer::class;
    }

    public function getTerms()
    {
        return $this->terms && json_decode($this->terms) > 0 ? json_decode($this->terms) : [];
    }

    public function getUserFilters(): array
    {
        return json_decode($this->user_filters, 1) ?: [];
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

        $start = $user_filters["registration_within"]['start'];
        $end = $user_filters["registration_within"]['end'];

        return (new TimeFrame())->forDateRange($start, $end);
    }

    /**
     * @return Carbon
     */
    public function getCashbackValidity()
    {
        $valid_till = null;

        if ($this->valid_till_date) {
            $valid_till = $this->valid_till_date;
        } elseif ($this->valid_till_day) {
            $valid_till = Carbon::now()->addDays($this->valid_till_day);
        }

        return $valid_till->endOfDay();
    }
}
