<?php namespace Sheba\Partner\DataMigration\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
    }
}