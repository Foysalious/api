<?php

namespace App\Models;

use Database\Factories\PartnerWalletSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerWalletSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Create a new factory instance for the model.
     *
     * @return PartnerWalletSettingFactory
     */
    protected static function newFactory(): PartnerWalletSettingFactory
    {
        return new PartnerWalletSettingFactory();
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
