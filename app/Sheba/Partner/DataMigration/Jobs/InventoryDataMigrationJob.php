<?php namespace Sheba\Partner\DataMigration\Jobs;


use App\Jobs\Job;
use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Repositories\PartnerRepository;

class InventoryDataMigrationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $data;
    private $partner;

    public function __construct($partner, $data)
    {
        $this->partner = $partner;
        $this->data = $data;
    }

    public function handle()
    {
        $client = app(InventoryServerClient::class);
        $partnerRepository = app(PartnerRepository::class);
        $client->post('api/v1/partners/'.$this->partner->id.'/migrate', $this->data);
        $partnerRepository->update($this->partner, ['is_migration_completed' => 1]);
    }
}