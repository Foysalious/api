<?php namespace App\Models;

use Sheba\Dal\BaseModel;
use Sheba\Dal\PartnerOrderPayment\Events\PartnerOrderPaymentSaved;
use Sheba\Report\Updater\PartnerOrderPayment as ReportUpdater;
use Sheba\Report\Updater\UpdatesReport;

class PartnerOrderPayment extends BaseModel implements UpdatesReport
{
    use ReportUpdater;
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    protected static $savedEventClass = PartnerOrderPaymentSaved::class;

    public function partnerOrder()
    {
        return $this->belongsTo(PartnerOrder::class);
    }
}
