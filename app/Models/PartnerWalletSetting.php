<?php namespace App\Models;

use Database\Factories\PartnerWalletSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerWalletSetting extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return PartnerWalletSettingFactory
     */
    protected static function newFactory(): PartnerWalletSettingFactory
    {
        return new PartnerWalletSettingFactory();
    }
}
