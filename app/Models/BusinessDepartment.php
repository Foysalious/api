<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessDepartment extends Model
{
    protected $guarded = ['id',];
    protected $table = 'business_departments';

    public function businessRoles()
    {
        return $this->hasMany(BusinessRole::class);
    }
}