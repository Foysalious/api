<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankUser extends Model
{
    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
