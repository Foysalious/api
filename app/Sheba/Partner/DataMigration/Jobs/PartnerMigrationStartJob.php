<?php namespace Sheba\Partner\DataMigration\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\PartnerDataMigration\PartnerDataMigration;
use Sheba\Dal\PartnerDataMigration\Statuses;

class PartnerMigrationStartJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $partner;

    public function __construct($partner)
    {
        $this->partner = $partner;
        $this->connection = 'pos_rebuild_data_migration';
        $this->queue = 'pos_rebuild_data_migration';
    }

    public function handle()
    {
        $partnerDataMigration = PartnerDataMigration::where('partner_id', $this->partner->id)->first();
        $partnerDataMigration ? $partnerDataMigration->update(['status' => Statuses::INITIATED]) :
            PartnerDataMigration::create(['partner_id' => $this->partner->id, 'status' => Statuses::INITIATED]);
    }
}