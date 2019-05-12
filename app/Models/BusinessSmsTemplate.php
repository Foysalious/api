<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessSmsTemplate extends Model
{
    protected $guarded = ['id',];
    protected $table = 'business_sms_templates';

    public function businesses()
    {
        return $this->belongsTo(Business::class);
    }

}