<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Report\Updater\PartnerOrderPayment as ReportUpdater;

class PartnerOrderPayment extends Model
{
    use ReportUpdater;
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }
}
