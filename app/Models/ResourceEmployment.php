<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceEmployment extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['joined_at', 'left_at'];

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
