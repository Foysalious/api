<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CanTopUpUpdateLog extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $table = 'can_top_up_update_logs';


    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
