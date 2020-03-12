<?php namespace App\Jobs;

use App\Models\Partner;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeductPartnerImpression extends Job implements ShouldQueue
{
    private $partner_ids;
    private $impressionToDeduct;

    public function __construct(array $partner_ids, $impressionToDeduct)
    {
        $this->partner_ids = $partner_ids;
        $this->impressionToDeduct = $impressionToDeduct;
    }

    public function handle()
    {
        Partner::whereIn('id', $this->partner_ids)->where('current_impression', '>', 10)->decrement('current_impression', $this->impressionToDeduct);
    }
}
