<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileMobileBankInformation extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'profile_mobile_bank_informations';

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
