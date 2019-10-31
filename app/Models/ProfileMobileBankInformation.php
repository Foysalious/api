<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileMobileBankInformation extends Model
{
    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
