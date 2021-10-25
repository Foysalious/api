<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileBankInformation extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'profile_bank_informations';

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
