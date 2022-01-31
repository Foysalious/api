<?php namespace App\Models;

use Database\Factories\BusinessRoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessRole extends Model
{
    use HasFactory;

    protected $guarded = ['id',];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $table = config('database.connections.mysql.database') . '.business_roles';
        $this->setTable($table);
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function businessDepartment()
    {
        return $this->belongsTo(BusinessDepartment::class);
    }

    public function members()
    {
        return $this->hasMany(BusinessMember::class, 'business_role_id');
    }
    /**
     * Create a new factory instance for the model.
     *
     * @return BusinessRoleFactory
     */
    protected static function newFactory(): BusinessRoleFactory
    {
        return new BusinessRoleFactory();
    }
}
