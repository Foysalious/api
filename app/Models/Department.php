<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
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
}
