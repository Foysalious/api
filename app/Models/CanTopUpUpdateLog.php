<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanTopUpUpdateLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $dates = ['created_at'];
    protected $table = 'can_top_up_update_logs';

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
