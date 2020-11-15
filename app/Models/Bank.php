<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{

    protected $guarded = ['id'];

    public function users()
    {
        return $this->hasMany(BankUser::class);
    }
}
