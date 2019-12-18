<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TopUpBulkRequest extends Model
{
    protected $guarded = ['id'];
    protected $table = 'topup_bulk_requests';
    protected $dates = ['created_at', 'updated_at'];

    public function agent()
    {
        return $this->morphTo();
    }

}