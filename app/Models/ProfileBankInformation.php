<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileBankInformation extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
