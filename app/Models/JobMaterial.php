<?php namespace App\Models;

use Sheba\Dal\BaseModel;
use Sheba\Dal\JobMaterial\Events\JobMaterialSaved;
use Sheba\Report\Updater\JobMaterial as ReportUpdater;
use Sheba\Report\Updater\UpdatesReport;

class JobMaterial extends BaseModel implements UpdatesReport
{
    use ReportUpdater;

    protected $table = 'job_material';

    protected $fillable = ['job_id', 'material_name', 'material_price'];

    protected $casts = ['material_price' => 'double'];

    protected static $savedEventClass = JobMaterialSaved::class;

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
