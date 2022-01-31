<?php namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function flags()
    {
        return $this->hasMany(Flag::class);
    }

    public function raisedFlags()
    {
        return $this->hasMany(Flag::class, 'by_department_id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return DepartmentFactory
     */
    protected static function newFactory(): DepartmentFactory
    {
        return new DepartmentFactory();
    }
}
