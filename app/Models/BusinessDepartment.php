<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;
use Sheba\Dal\TripRequestApprovalFlow\Model as TripRequestApprovalFlow;

class BusinessDepartment extends Model
{
    protected $guarded = ['id',];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $table = config('database.connections.mysql.database') . '.business_departments';
        $this->setTable($table);
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function businessRoles()
    {
        return $this->hasMany(BusinessRole::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function tripRequestFlow()
    {
        return $this->hasOne(TripRequestApprovalFlow::class);
    }

    public function approvalFlows()
    {
        return $this->hasMany(ApprovalFlow::class);
    }

    public function approvalFlowBy($type)
    {
        return $this->approvalFlows()->where('type', $type)->first();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }
}
