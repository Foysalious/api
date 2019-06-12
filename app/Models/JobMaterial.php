<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Report\Updater\JobMaterial as ReportUpdater;

class JobMaterial extends Model
{
    use ReportUpdater;

    protected $table = 'job_material';

    protected $fillable = ['job_id', 'material_name', 'material_price'];

    protected $casts = ['material_price' => 'double'];

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
