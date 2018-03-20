<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerServiceDiscount extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['cap' => 'double'];

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
}
