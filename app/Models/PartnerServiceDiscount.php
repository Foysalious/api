<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PartnerServiceDiscount extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['cap' => 'double', 'amount' => 'double'];

    public function partnerService()
    {
        return $this->belongsTo(PartnerService::class);
    }

    public function getAmount($options = null)
    {
        $service_type = $this->partnerService->service->variable_type;
        $call_method = "getAmountFor" . $service_type;
        return $this->$call_method($options);
    }

    public function getAmountForFixed()
    {
        return floatval($this->amount);
    }

    public function getAmountForOptions($options = null)
    {
        if (!$options) {
            throw new \Exception("Options are required for 'Options' type services.");
        }

        if (is_array($options)) $options = implode(',', $options);
        return json_decode($this->amount, true)[$options];
    }

    public function isPercentage()
    {
        return $this->is_amount_percentage;
    }

    public function hasCap()
    {
        return $this->cap > 0;
    }

    public function scopeRunning($query)
    {
        $now = Carbon::now();
        return $query->where([['end_date', '>=', $now], ['start_date', '<=', $now]]);
    }
}
