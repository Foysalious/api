<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessMember extends Model
{
    protected $guarded = ['id',];
    protected $table = 'business_member';

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class);
    }
}