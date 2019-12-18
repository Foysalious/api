<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TopUpBulkRequestNumber extends Model
{
    protected $guarded = ['id'];
    protected $table = 'topup_bulk_request_numbers';
    protected $dates = ['created_at', 'updated_at'];

}