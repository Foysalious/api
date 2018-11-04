<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopUpOrder extends Model
{
    protected $guarded = ['id'];
    protected $table = 'topup_orders';
    protected $dates = ['created_at', 'updated_at'];

    public function agent()
    {
        return $this->morphTo();
    }

    public function vendor()
    {
        return $this->belongsTo(TopUpVendor::class);
    }

    public function getAgentNameAttribute()
    {
        $agent_type = explode('\\', $this->agent_type)[2];
        if ($agent_type == 'Partner') return $this->agent->name;
        elseif ($agent_type == 'Affiliate') return $this->agent->profile->name;
    }

    public function getAgentMobileAttribute()
    {
        $agent_type = explode('\\', $this->agent_type)[2];
        if ($agent_type == 'Partner') return $this->agent->contact_no;
        elseif ($agent_type == 'Affiliate') return $this->agent->profile->mobile;
    }

    public function isFailed()
    {
        return $this->status == 'Failed';
    }
}