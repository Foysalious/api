<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InspectionItemStatusLog extends Model
{
    protected $guarded = ['id',];
    protected $table = 'inspection_item_status_logs';
    public $timestamps = false;

}