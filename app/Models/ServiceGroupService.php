<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceGroupService extends Model
{
    protected $table = 'service_group_service';
    protected $fillable = ['service_group_id', 'service_id', 'order'];
    public $timestamps = false;
}
