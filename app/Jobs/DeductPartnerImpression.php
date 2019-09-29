<?php

namespace App\Jobs;


use App\Models\Partner;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeductPartnerImpression extends Job implements ShouldQueue
{
    private $partner_ids;

    public function __construct(array $partner_ids)
    {
        $this->partner_ids = $partner_ids;
    }

    public function handle()
    {
        Partner::whereIn('id', $this->partner_ids)->where('current_impression', '>', 10)->decrement('current_impression');
    }

}