<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\InfoCall\InfoCall;

use Carbon\Carbon;

class Complain extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['estimated_resolve_date', 'resolved_time'];

    public function complainable()
    {
        return $this->morphTo();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function setEstimatedResolveDateAttribute($date) {
        $this->attributes['estimated_resolve_date'] = Carbon::parse($date);
    }

    public function code()
    {
        return 'C-'. sprintf('%04d', $this->id);
    }

    public function complainableType()
    {
        $type_exploded = explode('\\', $this->complainable_type);
        return end($type_exploded);
    }

    public function complainableCode()
    {
        if(!$this->complainable) return "N/F";
        $model = $this->complainableType();
        return $model . ": " . $this->complainable->code();
    }

    public function complainableLink()
    {
        if($this->complainableType() == 'Job') {
            return url('job/' . $this->complainable_id);
        } elseif($this->complainableType() == 'InfoCall') {
            return url('info-call/' . $this->complainable_id);
        }
        return "#";
    }

    public function customer()
    {
        if($this->complainableType() == 'Job') {
            $customer = $this->complainable->partnerOrder->order->customer;
            return (object)[
                'id'        => $customer->id,
                'name'      => $customer->profile->name,
                'mobile'    => $customer->profile->mobile
            ];
        } else {
            $complainable = $this->complainable;
            return (object)[
                'name'      => $complainable->customer_name,
                'mobile'    => $complainable->customer_mobile
            ];
        }
    }

    public function statusChangeLog()
    {
        return $this->hasMany(ComplainStatusLog::class);
    }

    public function scopeStatus($query, $status)
    {
        $query->where('status', $status);
    }

    /**
     * Get complain from job
     *
     * @param $query
     */
    public function scopeJobComplainableType($query)
    {
        $query->where('complainable_type', 'App\Models\Job');
    }

    /**
     * Get complain from infocall
     *
     * @param $query
     */
    public function scopeInfocallComplainableType($query)
    {
        $query->where('complainable_type', InfoCall::class);
    }

    public function mentions()
    {
        return $this->morphMany(Mention::class, 'mentionable');
    }
}
