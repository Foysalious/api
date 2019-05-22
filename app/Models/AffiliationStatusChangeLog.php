<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliationStatusChangeLog extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class);
    }

    public static function boot()
    {
        parent::boot();

        self::created(function(AffiliationStatusChangeLog $model){
            $model->affiliation->createOrUpdateReport();
        });

        self::updated(function(AffiliationStatusChangeLog $model){
            $model->affiliation->createOrUpdateReport();
        });
    }
}
