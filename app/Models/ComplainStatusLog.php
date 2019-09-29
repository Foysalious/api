<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplainStatusLog extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at'];
    protected $guarded = ['id'];

    public function complain()
    {
        return $this->belongsTo(Complain::class);
    }
}
