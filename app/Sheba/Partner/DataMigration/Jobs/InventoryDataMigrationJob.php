<?php namespace Sheba\Partner\DataMigration\Jobs;


use App\Jobs\Job;
use App\Sheba\InventoryService\InventoryServerClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InventoryDataMigrationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $data;
    private $partnerId;

    public function __construct($partnerId, $data)
    {
        $this->partnerId = $partnerId;
        $this->data = $data;
    }

    public function handle()
    {
        $client = app(InventoryServerClient::class);
        $client->post('api/v1/partners/'.$this->partnerId.'/migrate', $this->data);
    }
}