<?php namespace App\Models;

use Database\Factories\ProfileBankInformationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileBankInformation extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'profile_bank_informations';

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return ProfileBankInformationFactory
     */
    protected static function newFactory(): ProfileBankInformationFactory
    {
        return new ProfileBankInformationFactory();
    }
}
