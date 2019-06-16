<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Report\Updater\AffiliationStatusChangeLog as ReportUpdater;

class AffiliationStatusChangeLog extends Model
{
    use ReportUpdater;
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class);
    }
}
