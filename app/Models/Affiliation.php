<?php namespace App\Models;

use Carbon\Carbon;
use Sheba\Dal\Affiliation\Events\AffiliationSaved;
use Sheba\Dal\AffiliationStatusChangeLog\AffiliationStatusChangeLog;
use Sheba\Dal\BaseModel;
use Sheba\Report\Updater\Affiliation as ReportUpdater;
use Sheba\Report\Updater\UpdatesReport;

class Affiliation extends BaseModel implements UpdatesReport
{
    use ReportUpdater;

    protected $guarded = ['id'];

    public static $savedEventClass = AffiliationSaved::class;

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function statusChangeLogs()
    {
        return $this->hasMany(AffiliationStatusChangeLog::class);
    }

    public function transactions()
    {
        return $this->morphMany(AffiliateTransaction::class, 'affiliation');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'successful');
    }

    public function scopeInPreviousMonth($query)
    {
        return $query->whereMonth('created_at', '=', Carbon::yesterday()->month)
            ->whereYear('created_at', '=', Carbon::yesterday()->year);
    }

    public function scopeInYesterday($query)
    {
        return $query->whereDate('created_at', '=', Carbon::yesterday());
    }

    public function scopeTotalRefer($query, $ambassador_id)
    {
        return $query
            ->leftJoin('affiliates', 'affiliates.id', '=', 'affiliations.affiliate_id')
            ->whereRaw('affiliations.affiliate_id IN ( SELECT id FROM affiliates WHERE ambassador_id = ? )
	AND affiliates.under_ambassador_since < affiliations.created_at', [$ambassador_id]);
    }

}
