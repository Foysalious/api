<?php namespace App\Sheba\Partner\DataMigration\Jobs;

use App\Jobs\Job;
use App\Sheba\PosOrderService\PosOrderServerClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PartnerDataMigrationToPosOrderJob extends Job implements ShouldQueue
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
        $client = app(PosOrderServerClient::class);
        $client->post('api/v1/partners/'.$this->partner->id.'/migrate', $this->data);
    }



}