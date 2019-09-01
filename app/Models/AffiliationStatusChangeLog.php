<?php namespace App\Models;

use Sheba\Dal\AffiliationStatusChangeLog\Events\AffiliationStatusChangeLogSaved;
use Sheba\Dal\BaseModel;
use Sheba\Report\Updater\AffiliationStatusChangeLog as ReportUpdater;
use Sheba\Report\Updater\UpdatesReport;

class AffiliationStatusChangeLog extends BaseModel implements UpdatesReport
{
    use ReportUpdater;

    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    protected static $savedEventClass = AffiliationStatusChangeLogSaved::class;

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class);
    }
}
