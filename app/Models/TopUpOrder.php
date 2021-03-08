<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\BaseModel;
use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Payment\PayableType;
use Sheba\TopUp\Gateway\Names;

class TopUpOrder extends BaseModel implements PayableType
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

    public function isAgentPartner()
    {
        return $this->agent_type == Partner::class;
    }

    public function isAgentAffiliate()
    {
        return $this->agent_type == Affiliate::class;
    }

    public function getAgentNameAttribute()
    {
        if ($this->isAgentPartner()) return $this->agent->name;
        elseif ($this->isAgentAffiliate()) return $this->agent->profile->name;
    }

    public function getAgentMobileAttribute()
    {
        if ($this->isAgentPartner()) return $this->agent->contact_no;
        elseif ($this->isAgentAffiliate()) return $this->agent->profile->mobile;
    }

    public function isFailed()
    {
        return $this->status == Statuses::FAILED;
    }

    public function isFailedDueToGatewayTimeout()
    {
        return $this->isFailed() && $this->failed_reason == FailedReason::GATEWAY_TIMEOUT;
    }

    public function isSuccess()
    {
        return $this->status == Statuses::SUCCESSFUL;
    }

    public function isProcessed()
    {
        return $this->isFailed() || $this->isSuccess();
    }

    public function scopeProcessed($query)
    {
        return $query->statuses(Statuses::getProcessed());
    }

    public function scopeStatus($query, $status)
    {
        return $query->whereIn('status', $status);
    }

    public function scopeStatuses($query, $statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeOperator($query, $vendor_id)
    {
        return $query->where('vendor_id', $vendor_id);
    }

    public function scopeGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function getOriginalMobile()
    {
        return getOriginalMobileNumber($this->payee_mobile);
    }

    public function isRobiWalletTopUp()
    {
        return !!$this->is_robi_topup_wallet;
    }

    public function isViaPaywell()
    {
        return $this->gateway == Names::PAYWELL;
    }

    public function getGatewayRefId()
    {
        return dechex($this->id);
    }
}
