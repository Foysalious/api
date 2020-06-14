<?php namespace Sheba\Referral;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasReferrals
{
    public function referrals(): HasMany;

    public function referredBy(): BelongsTo;

    public function usage():HasMany;
}
