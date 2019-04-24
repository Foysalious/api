<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessJoinRequest extends Model
{
    protected $guarded = ['id',];
    protected $table = 'business_join_requests';
}