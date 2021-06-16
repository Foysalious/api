<?php namespace Sheba\Partner\DataMigration\Jobs;

use App\Jobs\Job;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Sheba\Repositories\PartnerRepository;

class PartnerMigrationCompleteJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $partner;

    public function __construct($partner)
    {
        $this->partner = $partner;
    }

    public function handle()
    {
        $partnerRepository = app(PartnerRepository::class);
        $partnerRepository->update($this->partner, ['is_migration_completed' => 1]);
        Log::info('Ended Migration of Partner: #'.$this->partner->id. ' at '.Carbon::now());
    }
}